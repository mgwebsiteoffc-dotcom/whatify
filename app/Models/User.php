<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'status',
        'avatar', 'timezone', 'last_login_at', 'last_login_ip',
        'is_onboarded', 'onboarding_step', 'partner_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'is_onboarded' => 'boolean',
    ];

    // ---- Relationships ----

    public function business()
    {
        return $this->hasOne(Business::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsappAccount::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    public function team()
    {
        return $this->hasOne(Team::class);
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class, 'member_user_id');
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function notifications()
    {
        return $this->hasMany(PlatformNotification::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // ---- Helpers ----

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBusinessOwner(): bool
    {
        return $this->role === 'business_owner';
    }

    

    public function isAgent(): bool
    {
        return $this->role === 'team_agent';
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner';
    }

    public function getActiveSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }

    public function getWalletBalance(): float
    {
        return $this->wallet?->balance ?? 0;
    }

    public function getPlanLimit(string $feature): int
    {
        $subscription = $this->getActiveSubscription();
        if (!$subscription) return 0;

        $snapshot = $subscription->plan_snapshot ?? [];
        return $snapshot[$feature] ?? $subscription->plan->{$feature} ?? 0;
    }

    public function canUseFeature(string $feature): bool
    {
        $limit = $this->getPlanLimit($feature);
        if ($limit === -1) return true; // unlimited
        if ($limit === 0) return false;

        // Check current usage
        return match($feature) {
            'automation_flows' => $this->automations()->where('status', '!=', 'draft')->count() < $limit,
            'agents' => ($this->team?->members()->count() ?? 0) < $limit,
            'whatsapp_numbers' => $this->whatsappAccounts()->where('status', 'connected')->count() < $limit,
            'campaigns_per_month' => $this->campaigns()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count() < $limit,
            'contacts_limit' => $this->contacts()->count() < $limit,
            default => true,
        };
    }

    /**
     * Get the business owner for this user (for agents, get the parent)
     */
    public function getBusinessOwner(): ?User
    {
        if ($this->isBusinessOwner() || $this->isSuperAdmin()) {
            return $this;
        }

        if ($this->isAgent()) {
            $membership = $this->teamMemberships()->with('team.owner')->first();
            return $membership?->team?->owner;
        }

        return null;
    }
}