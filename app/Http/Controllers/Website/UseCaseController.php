<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;

class UseCaseController extends Controller
{
    protected array $useCases = [
        'ecommerce' => [
            'title' => 'WhatsApp Automation for E-Commerce',
            'headline' => 'Boost E-Commerce Sales with WhatsApp',
            'description' => 'Automate order confirmations, shipping updates, abandoned cart recovery and promotional campaigns for your online store.',
            'emoji' => '🛒',
            'features' => [
                ['Order Confirmation', 'Automatically send order details and thank you messages when customers place orders.'],
                ['Shipping Updates', 'Send real-time tracking information when orders are shipped and delivered.'],
                ['Abandoned Cart Recovery', 'Remind customers about items left in their cart and drive them back to purchase.'],
                ['COD Verification', 'Verify cash-on-delivery orders to reduce RTO and failed deliveries.'],
                ['Product Promotions', 'Send new arrivals, flash sales and exclusive offers directly on WhatsApp.'],
                ['Review Requests', 'Ask customers for reviews and ratings after delivery.'],
            ],
            'stats' => ['35% cart recovery rate', '98% message open rate', '45% increase in repeat purchases', '60% reduction in support tickets'],
            'integrations' => ['Shopify', 'WooCommerce', 'Magento'],
        ],
        'education' => [
            'title' => 'WhatsApp Automation for Education',
            'headline' => 'Transform Education Communication with WhatsApp',
            'description' => 'Automate admission inquiries, course updates, fee reminders and student engagement for educational institutions.',
            'emoji' => '🎓',
            'features' => [
                ['Admission Inquiry Bot', 'Automatically handle admission queries with course details, eligibility and fee information.'],
                ['Fee Reminders', 'Send automated fee due reminders and payment confirmations.'],
                ['Class Notifications', 'Notify students about schedule changes, assignments and exam dates.'],
                ['Result Updates', 'Share exam results and report cards directly on WhatsApp.'],
                ['Parent Communication', 'Keep parents informed about attendance, performance and school events.'],
                ['Course Catalog', 'Interactive catalog showing available courses with details and enrollment options.'],
            ],
            'stats' => ['50% reduction in inquiry response time', '80% parent engagement rate', '40% fewer missed fee payments', '90% message read rate'],
            'integrations' => ['Google Sheets', 'Custom API'],
        ],
        'healthcare' => [
            'title' => 'WhatsApp Automation for Healthcare',
            'headline' => 'Modernize Healthcare Communication',
            'description' => 'Streamline appointment booking, prescription reminders, lab reports and patient engagement via WhatsApp.',
            'emoji' => '🏥',
            'features' => [
                ['Appointment Booking', 'Let patients book, reschedule and cancel appointments via WhatsApp chatbot.'],
                ['Appointment Reminders', 'Reduce no-shows with automated appointment reminders 24 hours before.'],
                ['Prescription Reminders', 'Send medication reminders to ensure patients follow their treatment plans.'],
                ['Lab Reports', 'Share lab results and medical reports securely on WhatsApp.'],
                ['Doctor Availability', 'Show real-time doctor schedules and available time slots.'],
                ['Health Tips', 'Send seasonal health tips, vaccination reminders and wellness content.'],
            ],
            'stats' => ['70% reduction in no-shows', '85% patient satisfaction', '60% faster appointment booking', '50% fewer phone calls'],
            'integrations' => ['Google Sheets', 'Custom API'],
        ],
        'real-estate' => [
            'title' => 'WhatsApp Automation for Real Estate',
            'headline' => 'Close More Deals with WhatsApp',
            'description' => 'Capture property inquiries, schedule site visits, share virtual tours and nurture leads automatically.',
            'emoji' => '🏠',
            'features' => [
                ['Lead Capture Bot', 'Automatically collect buyer preferences, budget and location requirements.'],
                ['Property Catalog', 'Share property listings with images, details and pricing via interactive messages.'],
                ['Site Visit Booking', 'Let prospects book site visits through WhatsApp with automated confirmation.'],
                ['Virtual Tours', 'Share virtual tour videos and 360° images directly on WhatsApp.'],
                ['Follow-up Sequences', 'Automated follow-up messages to nurture leads over time.'],
                ['Agent Assignment', 'Route inquiries to the right sales agent based on location or property type.'],
            ],
            'stats' => ['3x more lead conversions', '80% faster response time', '50% more site visits', '40% reduction in lead drop-off'],
            'integrations' => ['Google Sheets', 'Custom CRM'],
        ],
        'restaurant' => [
            'title' => 'WhatsApp Automation for Restaurants',
            'headline' => 'Serve Customers Better with WhatsApp',
            'description' => 'Take orders, share menus, manage reservations and collect feedback — all through WhatsApp.',
            'emoji' => '🍽️',
            'features' => [
                ['Digital Menu', 'Share your interactive menu with images and prices via WhatsApp.'],
                ['Order Taking', 'Accept orders directly through WhatsApp chatbot with customization options.'],
                ['Table Reservations', 'Let customers book tables, choose time slots and get confirmation.'],
                ['Order Status', 'Send real-time order preparation and delivery status updates.'],
                ['Loyalty Programs', 'Run loyalty point programs and exclusive member offers on WhatsApp.'],
                ['Feedback Collection', 'Collect ratings and reviews after every order or visit.'],
            ],
            'stats' => ['30% increase in online orders', '95% customer satisfaction', '50% fewer missed reservations', '25% increase in repeat orders'],
            'integrations' => ['Google Sheets', 'Custom POS'],
        ],
        'travel' => [
            'title' => 'WhatsApp Automation for Travel & Tourism',
            'headline' => 'Elevate Travel Experiences with WhatsApp',
            'description' => 'Handle package inquiries, booking confirmations, travel updates and customer support on WhatsApp.',
            'emoji' => '✈️',
            'features' => [
                ['Package Inquiry Bot', 'Automatically handle destination, budget and travel date inquiries.'],
                ['Booking Confirmation', 'Send detailed booking confirmations with itinerary and documents.'],
                ['Travel Updates', 'Notify travelers about flight changes, weather alerts and local tips.'],
                ['Visa Assistance', 'Guide customers through visa requirements and document checklists.'],
                ['Review Collection', 'Request post-trip reviews and testimonials.'],
                ['Emergency Support', 'Provide 24/7 emergency contact and support via WhatsApp.'],
            ],
            'stats' => ['40% faster booking process', '85% customer engagement', '60% fewer support calls', '35% increase in repeat bookings'],
            'integrations' => ['Google Sheets', 'Custom API'],
        ],
    ];

    public function index()
    {
        $useCases = $this->useCases;
        return view('website.usecases.index', compact('useCases'));
    }

    public function show(string $slug)
    {
        if (!isset($this->useCases[$slug])) {
            abort(404);
        }

        $useCase = $this->useCases[$slug];
        $useCase['slug'] = $slug;
        $otherUseCases = collect($this->useCases)->except($slug)->take(3);

        return view('website.usecases.show', compact('useCase', 'otherUseCases'));
    }

    public function industries()
    {
        $useCases = $this->useCases;
        return view('website.industries', compact('useCases'));
    }

    public function industryShow(string $slug)
    {
        return $this->show($slug);
    }
}