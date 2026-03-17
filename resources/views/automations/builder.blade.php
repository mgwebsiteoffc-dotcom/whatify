@extends('layouts.app')
@section('title', 'Flow Builder - ' . $automation->name)
@section('page-title')
    <a href="{{ route('automations.show', $automation) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Flow Builder: {{ $automation->name }}
@endsection

@section('content')
<div x-data="flowBuilder()" x-init="init()" class="space-y-4">

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{-- Trigger Config --}}
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-600">Trigger:</span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700">
                    <i class="fas fa-bolt mr-1"></i>{{ ucfirst(str_replace('_', ' ', $automation->trigger_type)) }}
                </span>
            </div>

            @if($automation->trigger_type === 'keyword')
                <div class="flex items-center gap-2">
                    <input type="text" x-model="keywordsInput" placeholder="Keywords (comma separated)"
                           class="rounded-md border-gray-300 text-xs px-3 py-1.5 border w-64">
                    <select x-model="matchType" class="rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                        <option value="contains">Contains</option>
                        <option value="exact">Exact Match</option>
                        <option value="starts_with">Starts With</option>
                    </select>
                </div>
            @endif

            {{-- WhatsApp Account --}}
            <select x-model="whatsappAccountId" class="rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                <option value="">Default Account</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $automation->whatsapp_account_id == $acc->id ? 'selected' : '' }}>
                        {{ $acc->display_name ?? $acc->phone_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button @click="addStep('send_message')" class="px-3 py-1.5 border rounded-md text-xs hover:bg-gray-50">
                <i class="fas fa-plus mr-1"></i> Add Step
            </button>
            <button @click="saveFlow()" :disabled="saving" class="px-4 py-1.5 bg-emerald-600 text-white rounded-md text-xs hover:bg-emerald-700 disabled:opacity-50">
                <span x-show="!saving"><i class="fas fa-save mr-1"></i> Save Flow</span>
                <span x-show="saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
            </button>
        </div>
    </div>

    {{-- Step Palette --}}
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs font-medium text-gray-500 uppercase mb-3">Add Step (click to add)</p>
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['send_message', '💬 Send Message', 'bg-green-50 text-green-700 border-green-200'],
                ['send_template', '📋 Send Template', 'bg-blue-50 text-blue-700 border-blue-200'],
                ['send_media', '📎 Send Media', 'bg-purple-50 text-purple-700 border-purple-200'],
                ['ask_question', '❓ Ask Question', 'bg-yellow-50 text-yellow-700 border-yellow-200'],
                ['buttons', '🔘 Buttons', 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                ['list_menu', '📜 List Menu', 'bg-pink-50 text-pink-700 border-pink-200'],
                ['condition', '🔀 Condition', 'bg-orange-50 text-orange-700 border-orange-200'],
                ['delay', '⏳ Delay', 'bg-gray-50 text-gray-700 border-gray-200'],
                ['add_tag', '🏷️ Add Tag', 'bg-teal-50 text-teal-700 border-teal-200'],
                ['remove_tag', '🗑️ Remove Tag', 'bg-red-50 text-red-700 border-red-200'],
                ['assign_agent', '👤 Assign Agent', 'bg-cyan-50 text-cyan-700 border-cyan-200'],
                ['transfer_to_agent', '🤝 Transfer', 'bg-amber-50 text-amber-700 border-amber-200'],
                ['api_call', '🔌 API Call', 'bg-violet-50 text-violet-700 border-violet-200'],
                ['webhook', '🪝 Webhook', 'bg-lime-50 text-lime-700 border-lime-200'],
                ['set_variable', '📝 Set Variable', 'bg-sky-50 text-sky-700 border-sky-200'],
                ['update_contact', '✏️ Update Contact', 'bg-rose-50 text-rose-700 border-rose-200'],
                ['end_flow', '🛑 End Flow', 'bg-red-50 text-red-700 border-red-200'],
            ] as [$type, $label, $classes])
                <button @click="addStep('{{ $type }}')"
                        class="px-3 py-1.5 rounded-md border text-xs font-medium hover:shadow-sm transition-shadow {{ $classes }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Flow Steps --}}
    <div class="space-y-3">
        <template x-if="steps.length === 0">
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-project-diagram text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Steps Yet</h3>
                <p class="text-gray-500 text-sm mb-4">Click any step type above to start building your flow</p>
            </div>
        </template>

        <template x-for="(step, index) in steps" :key="step.step_id">
            <div class="bg-white rounded-lg shadow overflow-hidden" :class="{'ring-2 ring-emerald-400': editingStep === step.step_id}">
                {{-- Step Header --}}
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b cursor-move">
                    <div class="flex items-center gap-3">
                        <span class="h-7 w-7 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700" x-text="index + 1"></span>
                        <span class="text-sm font-medium text-gray-700 capitalize" x-text="step.type.replace(/_/g, ' ')"></span>
                        <span class="text-xs text-gray-400 font-mono" x-text="'#' + step.step_id.substring(0, 6)"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="editingStep = editingStep === step.step_id ? null : step.step_id" class="p-1.5 hover:bg-gray-200 rounded text-xs text-gray-500">
                            <i class="fas" :class="editingStep === step.step_id ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                        <button @click="moveStep(index, -1)" x-show="index > 0" class="p-1.5 hover:bg-gray-200 rounded text-xs text-gray-500"><i class="fas fa-arrow-up"></i></button>
                        <button @click="moveStep(index, 1)" x-show="index < steps.length - 1" class="p-1.5 hover:bg-gray-200 rounded text-xs text-gray-500"><i class="fas fa-arrow-down"></i></button>
                        <button @click="duplicateStep(index)" class="p-1.5 hover:bg-gray-200 rounded text-xs text-gray-500"><i class="fas fa-copy"></i></button>
                        <button @click="removeStep(index)" class="p-1.5 hover:bg-red-100 rounded text-xs text-red-500"><i class="fas fa-trash"></i></button>
                    </div>
                </div>

                {{-- Step Config --}}
                <div x-show="editingStep === step.step_id" x-cloak class="p-4 space-y-3">

                    {{-- Send Message --}}
                    <template x-if="step.type === 'send_message'">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Message Text</label>
                            <textarea x-model="step.config.message" rows="3" placeholder="Hello {{name}}, welcome to our store!"
                                      class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                            <p class="text-[10px] text-gray-400 mt-1">Variables: {{name}}, {{phone}}, {{email}}, {{custom.field_name}}</p>
                        </div>
                    </template>

                    {{-- Send Template --}}
                    <template x-if="step.type === 'send_template'">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Template</label>
                                <select x-model="step.config.template_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                    <option value="">Select template</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->category }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Body Parameters (comma separated)</label>
                                <input type="text" x-model="step.config.body_params_text" placeholder="{{name}}, Order #123"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                    </template>

                    {{-- Send Media --}}
                    <template x-if="step.type === 'send_media'">
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Media Type</label>
                                    <select x-model="step.config.media_type" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                        <option value="document">Document</option>
                                        <option value="audio">Audio</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Media URL</label>
                                    <input type="text" x-model="step.config.media_url" placeholder="https://..."
                                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Caption</label>
                                <input type="text" x-model="step.config.caption" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                    </template>

                    {{-- Ask Question --}}
                    <template x-if="step.type === 'ask_question'">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                                <textarea x-model="step.config.question" rows="2" placeholder="What is your name?"
                                          class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Save Response As</label>
                                    <input type="text" x-model="step.config.variable_name" placeholder="customer_name"
                                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Validation</label>
                                    <select x-model="step.config.validation_type" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                        <option value="">None</option>
                                        <option value="email">Email</option>
                                        <option value="phone">Phone</option>
                                        <option value="number">Number</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Error Message (if validation fails)</label>
                                <input type="text" x-model="step.config.validation_error" placeholder="Please enter a valid email"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                    </template>

                    {{-- Buttons --}}
                    <template x-if="step.type === 'buttons'">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Body Text</label>
                                <textarea x-model="step.config.body" rows="2" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Header (optional)</label>
                                    <input type="text" x-model="step.config.header" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Footer (optional)</label>
                                    <input type="text" x-model="step.config.footer" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-xs font-medium text-gray-600">Buttons (max 3)</label>
                                    <button @click="if(!step.config.buttons) step.config.buttons = []; if(step.config.buttons.length < 3) step.config.buttons.push({id: 'btn_'+Date.now(), title: ''})"
                                            class="text-[10px] text-emerald-600 hover:text-emerald-700"><i class="fas fa-plus mr-1"></i>Add</button>
                                </div>
                                <template x-for="(btn, bi) in (step.config.buttons || [])" :key="bi">
                                    <div class="flex gap-2 mb-1">
                                        <input type="text" x-model="btn.id" placeholder="btn_id" class="w-24 rounded-md border-gray-300 text-xs px-2 py-1.5 border font-mono">
                                        <input type="text" x-model="btn.title" placeholder="Button label" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                        <button @click="step.config.buttons.splice(bi, 1)" class="text-red-400 hover:text-red-600 px-1"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="text-xs font-medium text-gray-600">Button Branches (route by response)</label>
                                    <button @click="if(!step.branches) step.branches = []; step.branches.push({value: '', next_step_id: ''})"
                                            class="text-[10px] text-emerald-600"><i class="fas fa-plus mr-1"></i>Add Branch</button>
                                </div>
                                <template x-for="(br, bi) in (step.branches || [])" :key="bi">
                                    <div class="flex gap-2 mb-1">
                                        <input type="text" x-model="br.value" placeholder="Button ID or * for default"
                                               class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                        <span class="text-xs text-gray-400 self-center">→</span>
                                        <select x-model="br.next_step_id" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                            <option value="">Select step</option>
                                            <template x-for="s in steps" :key="s.step_id">
                                                <option :value="s.step_id" x-text="s.type.replace(/_/g,' ') + ' #' + s.step_id.substring(0,6)"></option>
                                            </template>
                                        </select>
                                        <button @click="step.branches.splice(bi, 1)" class="text-red-400 px-1"><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- List Menu --}}
                    <template x-if="step.type === 'list_menu'">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Body Text</label>
                                <textarea x-model="step.config.body" rows="2" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Button Text</label>
                                <input type="text" x-model="step.config.button_text" placeholder="View Menu" maxlength="20"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                            <p class="text-[10px] text-gray-400">Sections/rows defined in JSON format in advanced mode</p>
                        </div>
                    </template>

                    {{-- Condition --}}
                    <template x-if="step.type === 'condition'">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-medium text-gray-600">Condition Branches</label>
                                <button @click="if(!step.branches) step.branches = []; step.branches.push({field:'', operator:'equals', value:'', next_step_id:''})"
                                        class="text-[10px] text-emerald-600"><i class="fas fa-plus mr-1"></i>Add Condition</button>
                            </div>
                            <template x-for="(br, bi) in (step.branches || [])" :key="bi">
                                <div class="flex gap-1 mb-2 p-2 bg-gray-50 rounded items-center flex-wrap">
                                    <span class="text-xs text-gray-500">If</span>
                                    <input type="text" x-model="br.field" placeholder="var.name or contact.name"
                                           class="w-32 rounded border-gray-300 text-xs px-2 py-1 border">
                                    <select x-model="br.operator" class="rounded border-gray-300 text-xs px-2 py-1 border">
                                        <option value="equals">equals</option>
                                        <option value="not_equals">not equals</option>
                                        <option value="contains">contains</option>
                                        <option value="greater_than">greater than</option>
                                        <option value="less_than">less than</option>
                                        <option value="is_empty">is empty</option>
                                        <option value="has_tag">has tag</option>
                                    </select>
                                    <input type="text" x-model="br.value" placeholder="value"
                                           class="w-24 rounded border-gray-300 text-xs px-2 py-1 border">
                                    <span class="text-xs text-gray-400">→</span>
                                    <select x-model="br.next_step_id" class="flex-1 rounded border-gray-300 text-xs px-2 py-1 border">
                                        <option value="">Select step</option>
                                        <template x-for="s in steps" :key="s.step_id">
                                            <option :value="s.step_id" x-text="s.type.replace(/_/g,' ') + ' #' + s.step_id.substring(0,6)"></option>
                                        </template>
                                    </select>
                                    <button @click="step.branches.splice(bi, 1)" class="text-red-400 px-1"><i class="fas fa-times text-xs"></i></button>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Delay --}}
                    <template x-if="step.type === 'delay'">
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                                <input type="number" x-model="step.config.value" min="1" placeholder="5"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Unit</label>
                                <select x-model="step.config.unit" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                    <option value="seconds">Seconds</option>
                                    <option value="minutes">Minutes</option>
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    {{-- Set Variable --}}
                    <template x-if="step.type === 'set_variable'">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Variable Name</label>
                                <input type="text" x-model="step.config.variable_name" placeholder="my_var" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Value</label>
                                <input type="text" x-model="step.config.value" placeholder="{{name}} or static text" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                    </template>

                    {{-- Add/Remove Tag --}}
                    <template x-if="step.type === 'add_tag' || step.type === 'remove_tag'">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Select Tag</label>
                            <select x-model="step.config.tag_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                <option value="">Choose tag</option>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    {{-- Assign Agent / Transfer --}}
                    <template x-if="step.type === 'assign_agent' || step.type === 'transfer_to_agent'">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Agent</label>
                                <select x-model="step.config.agent_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                    <option value="">Auto-assign</option>
                                    @foreach($teamMembers as $member)
                                        <option value="{{ $member->member->id }}">{{ $member->member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <template x-if="step.type === 'transfer_to_agent'">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Pause Bot For (hours)</label>
                                    <input type="number" x-model="step.config.pause_hours" placeholder="24" min="1"
                                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- API Call --}}
                    <template x-if="step.type === 'api_call'">
                        <div class="space-y-2">
                            <div class="grid grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
                                    <select x-model="step.config.method" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                        <option value="get">GET</option>
                                        <option value="post">POST</option>
                                        <option value="put">PUT</option>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">URL</label>
                                    <input type="text" x-model="step.config.url" placeholder="https://api.example.com/endpoint"
                                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Save Response To Variable</label>
                                <input type="text" x-model="step.config.response_variable" placeholder="api_response"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                            </div>
                        </div>
                    </template>

                    {{-- Webhook --}}
                    <template x-if="step.type === 'webhook'">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Webhook URL</label>
                            <input type="text" x-model="step.config.url" placeholder="https://your-server.com/webhook"
                                   class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </template>

                    {{-- Update Contact --}}
                    <template x-if="step.type === 'update_contact'">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Field</label>
                                <select x-model="step.config.field" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                    <option value="name">Name</option>
                                    <option value="email">Email</option>
                                    <option value="city">City (custom)</option>
                                    <option value="company">Company (custom)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Value</label>
                                <input type="text" x-model="step.config.value" placeholder="{{customer_name}}"
                                       class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                    </template>

                    {{-- End Flow --}}
                    <template x-if="step.type === 'end_flow'">
                        <p class="text-sm text-gray-500">This step ends the automation flow.</p>
                    </template>

                    {{-- Next Step Connector --}}
                    <template x-if="!['condition', 'buttons', 'end_flow', 'transfer_to_agent'].includes(step.type)">
                        <div class="pt-3 border-t">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Next Step →</label>
                            <select x-model="step.next_step_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                <option value="">End flow (no next step)</option>
                                <template x-for="s in steps.filter(s => s.step_id !== step.step_id)" :key="s.step_id">
                                    <option :value="s.step_id" x-text="s.type.replace(/_/g,' ') + ' #' + s.step_id.substring(0,6)"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>

                {{-- Step Connection Arrow --}}
                <template x-if="index < steps.length - 1 && step.next_step_id">
                    <div class="text-center py-1 text-gray-300">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Save Status --}}
    <div x-show="saveMessage" x-cloak class="fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50"
         :class="saveError ? 'bg-red-500 text-white' : 'bg-green-500 text-white'"
         x-text="saveMessage"
         x-transition></div>
</div>

@push('scripts')
<script>
function flowBuilder() {
    return {
        steps: @json($automation->steps->map(fn($s) => [
            'step_id' => $s->step_id,
            'type' => $s->type,
            'config' => $s->config ?? [],
            'next_step_id' => $s->next_step_id,
            'branches' => $s->branches ?? [],
            'position_x' => $s->position_x,
            'position_y' => $s->position_y,
            'sort_order' => $s->sort_order,
        ])),
        editingStep: null,
        saving: false,
        saveMessage: '',
        saveError: false,
        keywordsInput: '{{ implode(", ", $automation->trigger_config["keywords"] ?? []) }}',
        matchType: '{{ $automation->trigger_config["match_type"] ?? "contains" }}',
        whatsappAccountId: '{{ $automation->whatsapp_account_id ?? "" }}',

        init() {
            if (this.steps.length > 0) {
                this.editingStep = this.steps[0].step_id;
            }
        },

        generateId() {
            return 'step_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
        },

        addStep(type) {
            const step = {
                step_id: this.generateId(),
                type: type,
                config: this.getDefaultConfig(type),
                next_step_id: null,
                branches: ['condition', 'buttons', 'list_menu'].includes(type) ? [] : null,
                position_x: 0,
                position_y: this.steps.length * 150,
                sort_order: this.steps.length,
            };

            // Auto-connect to previous step
            if (this.steps.length > 0) {
                const lastStep = this.steps[this.steps.length - 1];
                if (!['condition', 'buttons', 'end_flow', 'transfer_to_agent'].includes(lastStep.type)) {
                    lastStep.next_step_id = step.step_id;
                }
            }

            this.steps.push(step);
            this.editingStep = step.step_id;
        },

        getDefaultConfig(type) {
            const defaults = {
                send_message: { message: '' },
                send_template: { template_id: '', body_params_text: '' },
                send_media: { media_type: 'image', media_url: '', caption: '' },
                ask_question: { question: '', variable_name: '', validation_type: '', validation_error: '' },
                buttons: { body: '', header: '', footer: '', buttons: [] },
                list_menu: { body: '', button_text: 'Menu', header: '', footer: '', sections: [] },
                condition: {},
                delay: { value: 5, unit: 'seconds' },
                set_variable: { variable_name: '', value: '' },
                add_tag: { tag_id: '' },
                remove_tag: { tag_id: '' },
                assign_agent: { agent_id: '' },
                transfer_to_agent: { agent_id: '', pause_hours: 24 },
                api_call: { method: 'get', url: '', headers: {}, body: {}, response_variable: '' },
                webhook: { url: '' },
                update_contact: { field: 'name', value: '' },
                goto_step: { target_step_id: '' },
                end_flow: {},
            };
            return defaults[type] || {};
        },

        removeStep(index) {
            if (!confirm('Remove this step?')) return;
            const removedId = this.steps[index].step_id;
            this.steps.splice(index, 1);
            // Clean references
            this.steps.forEach(s => {
                if (s.next_step_id === removedId) s.next_step_id = null;
                if (s.branches) {
                    s.branches.forEach(b => {
                        if (b.next_step_id === removedId) b.next_step_id = '';
                    });
                }
            });
        },

        duplicateStep(index) {
            const original = this.steps[index];
            const newStep = JSON.parse(JSON.stringify(original));
            newStep.step_id = this.generateId();
            newStep.next_step_id = null;
            this.steps.splice(index + 1, 0, newStep);
            this.editingStep = newStep.step_id;
        },

        moveStep(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.steps.length) return;
            const temp = this.steps[index];
            this.steps[index] = this.steps[newIndex];
            this.steps[newIndex] = temp;
            // Update sort orders
            this.steps.forEach((s, i) => s.sort_order = i);
        },

        async saveFlow() {
            this.saving = true;
            this.saveMessage = '';

            // Build trigger config
            let triggerConfig = {};
            if ('{{ $automation->trigger_type }}' === 'keyword') {
                triggerConfig = {
                    keywords: this.keywordsInput.split(',').map(k => k.trim()).filter(k => k),
                    match_type: this.matchType,
                };
            }

            // Prepare steps - resolve template params
            const stepsData = this.steps.map((s, i) => {
                const step = { ...s, sort_order: i };
                if (s.type === 'send_template' && s.config.body_params_text) {
                    step.config.body_params = s.config.body_params_text.split(',').map(p => p.trim());
                }
                return step;
            });

            try {
                const response = await fetch('{{ route("automations.saveFlow", $automation) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        trigger_config: triggerConfig,
                        whatsapp_account_id: this.whatsappAccountId || null,
                        steps: stepsData,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.saveMessage = '✓ Flow saved successfully!';
                    this.saveError = false;
                } else {
                    this.saveMessage = '✗ Failed to save: ' + (data.message || 'Unknown error');
                    this.saveError = true;
                }
            } catch (e) {
                this.saveMessage = '✗ Error: ' + e.message;
                this.saveError = true;
            }

            this.saving = false;
            setTimeout(() => this.saveMessage = '', 3000);
        }
    }
}
</script>
@endpush
@endsection