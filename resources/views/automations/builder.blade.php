@extends('layouts.app')
@section('title', 'Flow Builder - ' . $automation->name)
@section('page-title')
    <a href="{{ route('automations.show', $automation) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Flow Builder: {{ $automation->name }}
@endsection

@section('content')
@php
    $stepsJson = $automation->steps->map(function($s) {
        return [
            'step_id' => $s->step_id,
            'type' => $s->type,
            'config' => $s->config ?? (object)[],
            'next_step_id' => $s->next_step_id,
            'branches' => $s->branches ?? [],
            'position_x' => $s->position_x,
            'position_y' => $s->position_y,
            'sort_order' => $s->sort_order,
        ];
    })->values()->toArray();

    $keywordsStr = implode(', ', $automation->trigger_config['keywords'] ?? []);
    $matchTypeStr = $automation->trigger_config['match_type'] ?? 'contains';
    $triggerType = $automation->trigger_type;
    $waAccountId = $automation->whatsapp_account_id ?? '';
    $saveFlowUrl = route('automations.saveFlow', $automation);
    $csrfToken = csrf_token();
@endphp

<div x-data="flowBuilder()" x-init="init()" class="space-y-4">

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg shadow p-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-600">Trigger:</span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700">
                <i class="fas fa-bolt mr-1"></i>{{ ucfirst(str_replace('_', ' ', $triggerType)) }}
            </span>

            @if($triggerType === 'keyword')
                <input type="text" x-model="keywordsInput" placeholder="Keywords (comma separated)"
                       class="rounded-md border-gray-300 text-xs px-3 py-1.5 border w-48">
                <select x-model="matchType" class="rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                    <option value="contains">Contains</option>
                    <option value="exact">Exact</option>
                    <option value="starts_with">Starts With</option>
                </select>
            @endif

            <select x-model="whatsappAccountId" class="rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                <option value="">Default Account</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $waAccountId == $acc->id ? 'selected' : '' }}>
                        {{ $acc->display_name ?? $acc->phone_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button @click="saveFlow()" :disabled="saving"
                    class="px-4 py-1.5 bg-emerald-600 text-white rounded-md text-xs hover:bg-emerald-700 disabled:opacity-50 font-medium">
                <span x-show="!saving"><i class="fas fa-save mr-1"></i> Save Flow</span>
                <span x-show="saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
            </button>
        </div>
    </div>

    {{-- Step Palette --}}
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-xs font-medium text-gray-500 uppercase mb-3">Click to add step</p>
        <div class="flex flex-wrap gap-2">
            <button @click="addStep('send_message')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-green-50 text-green-700 border-green-200 hover:shadow-sm">💬 Message</button>
            <button @click="addStep('send_template')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-blue-50 text-blue-700 border-blue-200 hover:shadow-sm">📋 Template</button>
            <button @click="addStep('send_media')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-purple-50 text-purple-700 border-purple-200 hover:shadow-sm">📎 Media</button>
            <button @click="addStep('ask_question')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-yellow-50 text-yellow-700 border-yellow-200 hover:shadow-sm">❓ Question</button>
            <button @click="addStep('buttons')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-indigo-50 text-indigo-700 border-indigo-200 hover:shadow-sm">🔘 Buttons</button>
            <button @click="addStep('list_menu')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-pink-50 text-pink-700 border-pink-200 hover:shadow-sm">📜 List</button>
            <button @click="addStep('condition')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-orange-50 text-orange-700 border-orange-200 hover:shadow-sm">🔀 Condition</button>
            <button @click="addStep('delay')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-gray-50 text-gray-700 border-gray-200 hover:shadow-sm">⏳ Delay</button>
            <button @click="addStep('add_tag')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-teal-50 text-teal-700 border-teal-200 hover:shadow-sm">🏷️ Add Tag</button>
            <button @click="addStep('remove_tag')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-red-50 text-red-700 border-red-200 hover:shadow-sm">🗑️ Remove Tag</button>
            <button @click="addStep('assign_agent')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-cyan-50 text-cyan-700 border-cyan-200 hover:shadow-sm">👤 Assign</button>
            <button @click="addStep('transfer_to_agent')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-amber-50 text-amber-700 border-amber-200 hover:shadow-sm">🤝 Transfer</button>
            <button @click="addStep('api_call')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-violet-50 text-violet-700 border-violet-200 hover:shadow-sm">🔌 API</button>
            <button @click="addStep('webhook')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-lime-50 text-lime-700 border-lime-200 hover:shadow-sm">🪝 Webhook</button>
            <button @click="addStep('set_variable')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-sky-50 text-sky-700 border-sky-200 hover:shadow-sm">📝 Variable</button>
            <button @click="addStep('update_contact')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-rose-50 text-rose-700 border-rose-200 hover:shadow-sm">✏️ Update</button>
            <button @click="addStep('end_flow')" class="px-3 py-1.5 rounded-md border text-xs font-medium bg-red-50 text-red-700 border-red-200 hover:shadow-sm">🛑 End</button>
        </div>
    </div>

    {{-- Empty State --}}
    <template x-if="steps.length === 0">
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-project-diagram text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Steps Yet</h3>
            <p class="text-gray-500 text-sm">Click any step type above to build your flow</p>
        </div>
    </template>

    {{-- Steps --}}
    @verbatim
    <template x-for="(step, index) in steps" :key="step.step_id">
        <div class="bg-white rounded-lg shadow overflow-hidden" :class="editingStep === step.step_id ? 'ring-2 ring-emerald-400' : ''">

            <!-- Step Header -->
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
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

            <!-- Step Config -->
            <div x-show="editingStep === step.step_id" x-cloak class="p-4 space-y-3">

                <!-- Send Message -->
                <template x-if="step.type === 'send_message'">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Message Text</label>
                        <textarea x-model="step.config.message" rows="3" :placeholder="PLACEHOLDERS.message"
                                  class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                        <p class="text-xs text-gray-400 mt-1" x-text="PLACEHOLDERS.vars_help"></p>
                    </div>
                </template>

                <!-- Send Template -->
                <template x-if="step.type === 'send_template'">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Template</label>
                            <select x-model="step.config.template_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                <option value="">Select template</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Templates are loaded from your WhatsApp account</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Body Params (comma separated)</label>
                            <input type="text" x-model="step.config.body_params_text" :placeholder="PLACEHOLDERS.template_params"
                                   class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </template>

                <!-- Send Media -->
                <template x-if="step.type === 'send_media'">
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select x-model="step.config.media_type" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="document">Document</option>
                                    <option value="audio">Audio</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">URL</label>
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

                <!-- Ask Question -->
                <template x-if="step.type === 'ask_question'">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                            <textarea x-model="step.config.question" rows="2" placeholder="What is your name?"
                                      class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Save As Variable</label>
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
                            <label class="block text-xs font-medium text-gray-600 mb-1">Error Message</label>
                            <input type="text" x-model="step.config.validation_error" placeholder="Please enter valid input"
                                   class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </template>

                <!-- Buttons -->
                <template x-if="step.type === 'buttons'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Body Text</label>
                            <textarea x-model="step.config.body" rows="2" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Header</label>
                                <input type="text" x-model="step.config.header" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Footer</label>
                                <input type="text" x-model="step.config.footer" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-medium text-gray-600">Buttons (max 3)</label>
                                <button type="button" @click="addButton(step)" class="text-xs text-emerald-600"><i class="fas fa-plus mr-1"></i>Add</button>
                            </div>
                            <template x-for="(btn, bi) in (step.config.buttons || [])" :key="bi">
                                <div class="flex gap-2 mb-1">
                                    <input type="text" x-model="btn.id" placeholder="btn_id" class="w-20 rounded-md border-gray-300 text-xs px-2 py-1.5 border font-mono">
                                    <input type="text" x-model="btn.title" placeholder="Button label" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                    <button type="button" @click="step.config.buttons.splice(bi, 1)" class="text-red-400 hover:text-red-600 px-1"><i class="fas fa-times"></i></button>
                                </div>
                            </template>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-medium text-gray-600">Branches</label>
                                <button type="button" @click="addBranch(step)" class="text-xs text-emerald-600"><i class="fas fa-plus mr-1"></i>Add</button>
                            </div>
                            <template x-for="(br, bi) in (step.branches || [])" :key="bi">
                                <div class="flex gap-2 mb-1">
                                    <input type="text" x-model="br.value" placeholder="Button ID or *" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                    <span class="text-xs text-gray-400 self-center">→</span>
                                    <select x-model="br.next_step_id" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                                        <option value="">Select step</option>
                                        <template x-for="s in steps" :key="s.step_id">
                                            <option :value="s.step_id" x-text="getStepLabel(s)"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="step.branches.splice(bi, 1)" class="text-red-400 px-1"><i class="fas fa-times"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- List Menu -->
                <template x-if="step.type === 'list_menu'">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Body Text</label>
                            <textarea x-model="step.config.body" rows="2" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Button Text</label>
                            <input type="text" x-model="step.config.button_text" placeholder="View Menu" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </template>

                <!-- Condition -->
                <template x-if="step.type === 'condition'">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-medium text-gray-600">Conditions</label>
                            <button type="button" @click="addConditionBranch(step)" class="text-xs text-emerald-600"><i class="fas fa-plus mr-1"></i>Add</button>
                        </div>
                        <template x-for="(br, bi) in (step.branches || [])" :key="bi">
                            <div class="flex gap-1 mb-2 p-2 bg-gray-50 rounded items-center flex-wrap">
                                <span class="text-xs text-gray-500">If</span>
                                <input type="text" x-model="br.field" placeholder="var.name" class="w-28 rounded border-gray-300 text-xs px-2 py-1 border">
                                <select x-model="br.operator" class="rounded border-gray-300 text-xs px-2 py-1 border">
                                    <option value="equals">equals</option>
                                    <option value="not_equals">not equals</option>
                                    <option value="contains">contains</option>
                                    <option value="greater_than">&gt;</option>
                                    <option value="less_than">&lt;</option>
                                    <option value="is_empty">is empty</option>
                                    <option value="has_tag">has tag</option>
                                </select>
                                <input type="text" x-model="br.value" placeholder="value" class="w-20 rounded border-gray-300 text-xs px-2 py-1 border">
                                <span class="text-xs text-gray-400">→</span>
                                <select x-model="br.next_step_id" class="flex-1 rounded border-gray-300 text-xs px-2 py-1 border">
                                    <option value="">Select step</option>
                                    <template x-for="s in steps" :key="s.step_id">
                                        <option :value="s.step_id" x-text="getStepLabel(s)"></option>
                                    </template>
                                </select>
                                <button type="button" @click="step.branches.splice(bi, 1)" class="text-red-400 px-1"><i class="fas fa-times text-xs"></i></button>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Delay -->
                <template x-if="step.type === 'delay'">
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                            <input type="number" x-model="step.config.value" min="1" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
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

                <!-- Set Variable -->
                <template x-if="step.type === 'set_variable'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Variable Name</label>
                            <input type="text" x-model="step.config.variable_name" placeholder="my_var" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Value</label>
                            <input type="text" x-model="step.config.value" placeholder="static text" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </template>

                <!-- Add/Remove Tag -->
                <template x-if="step.type === 'add_tag' || step.type === 'remove_tag'">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tag</label>
                        <select x-model="step.config.tag_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="">Choose tag</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Or type tag name to auto-create</p>
                        <input type="text" x-model="step.config.tag_name" placeholder="Tag name (auto-create)" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </template>

                <!-- Assign / Transfer -->
                <template x-if="step.type === 'assign_agent' || step.type === 'transfer_to_agent'">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Agent</label>
                            <select x-model="step.config.agent_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                                <option value="">Auto-assign</option>
                            </select>
                        </div>
                        <template x-if="step.type === 'transfer_to_agent'">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Pause Bot (hours)</label>
                                <input type="number" x-model="step.config.pause_hours" placeholder="24" min="1" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </template>
                    </div>
                </template>

                <!-- API Call -->
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
                                <input type="text" x-model="step.config.url" placeholder="https://api.example.com" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Save Response As</label>
                            <input type="text" x-model="step.config.response_variable" placeholder="api_response" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                    </div>
                </template>

                <!-- Webhook -->
                <template x-if="step.type === 'webhook'">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Webhook URL</label>
                        <input type="text" x-model="step.config.url" placeholder="https://your-server.com/webhook" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </template>

                <!-- Update Contact -->
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
                            <input type="text" x-model="step.config.value" placeholder="Use variable name" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </template>

                <!-- End Flow -->
                <template x-if="step.type === 'end_flow'">
                    <p class="text-sm text-gray-500 italic">This step ends the automation flow.</p>
                </template>

                <!-- Next Step -->
                <template x-if="!['condition', 'buttons', 'end_flow', 'transfer_to_agent'].includes(step.type)">
                    <div class="pt-3 border-t">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Next Step →</label>
                        <select x-model="step.next_step_id" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="">End (no next step)</option>
                            <template x-for="s in steps.filter(s => s.step_id !== step.step_id)" :key="s.step_id">
                                <option :value="s.step_id" x-text="getStepLabel(s)"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>
        </div>
    </template>
    @endverbatim

    {{-- Toast --}}
    <div x-show="saveMessage" x-cloak
         class="fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50"
         :class="saveError ? 'bg-red-500 text-white' : 'bg-green-500 text-white'"
         x-text="saveMessage" x-transition></div>
</div>

@push('scripts')
<script>
// Placeholder strings with curly braces (escaped from Blade)
var WA_VAR_OPEN = String.fromCharCode(123, 123);
var WA_VAR_CLOSE = String.fromCharCode(125, 125);

function flowBuilder() {
    return {
        steps: @json($stepsJson),
        editingStep: null,
        saving: false,
        saveMessage: '',
        saveError: false,
        keywordsInput: @json($keywordsStr),
        matchType: @json($matchTypeStr),
        whatsappAccountId: @json($waAccountId),

        PLACEHOLDERS: {
            message: 'Hello ' + WA_VAR_OPEN + 'name' + WA_VAR_CLOSE + ', welcome to our store!',
            vars_help: 'Variables: ' + WA_VAR_OPEN + 'name' + WA_VAR_CLOSE + ', ' + WA_VAR_OPEN + 'phone' + WA_VAR_CLOSE + ', ' + WA_VAR_OPEN + 'email' + WA_VAR_CLOSE,
            template_params: WA_VAR_OPEN + 'name' + WA_VAR_CLOSE + ', Order #123',
        },

        init() {
            if (this.steps.length > 0) {
                this.editingStep = this.steps[0].step_id;
            }
            this.steps.forEach(function(s) {
                if (!s.config || typeof s.config !== 'object' || Array.isArray(s.config)) s.config = {};
                if (!s.branches || !Array.isArray(s.branches)) s.branches = [];
                if (s.type === 'buttons' && !Array.isArray(s.config.buttons)) s.config.buttons = [];
            });
        },

        generateId() {
            return 'step_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
        },

        getStepLabel(s) {
            return s.type.replace(/_/g, ' ') + ' #' + s.step_id.substring(0, 6);
        },

        getDefaultConfig(type) {
            var defaults = {
                send_message: { message: '' },
                send_template: { template_id: '', body_params_text: '' },
                send_media: { media_type: 'image', media_url: '', caption: '' },
                ask_question: { question: '', variable_name: '', validation_type: '', validation_error: '' },
                buttons: { body: '', header: '', footer: '', buttons: [] },
                list_menu: { body: '', button_text: 'Menu' },
                condition: {},
                delay: { value: 5, unit: 'seconds' },
                set_variable: { variable_name: '', value: '' },
                add_tag: { tag_id: '', tag_name: '' },
                remove_tag: { tag_id: '', tag_name: '' },
                assign_agent: { agent_id: '' },
                transfer_to_agent: { agent_id: '', pause_hours: 24 },
                api_call: { method: 'get', url: '', response_variable: '' },
                webhook: { url: '' },
                update_contact: { field: 'name', value: '' },
                end_flow: {},
            };
            return JSON.parse(JSON.stringify(defaults[type] || {}));
        },

        addStep(type) {
            var step = {
                step_id: this.generateId(),
                type: type,
                config: this.getDefaultConfig(type),
                next_step_id: null,
                branches: [],
                position_x: 0,
                position_y: this.steps.length * 150,
                sort_order: this.steps.length,
            };

            if (this.steps.length > 0) {
                var lastStep = this.steps[this.steps.length - 1];
                if (['condition', 'buttons', 'end_flow', 'transfer_to_agent'].indexOf(lastStep.type) === -1) {
                    lastStep.next_step_id = step.step_id;
                }
            }

            this.steps.push(step);
            this.editingStep = step.step_id;
        },

        addButton(step) {
            if (!step.config.buttons) step.config.buttons = [];
            if (step.config.buttons.length < 3) {
                step.config.buttons.push({ id: 'btn_' + Date.now(), title: '' });
            }
        },

        addBranch(step) {
            if (!step.branches) step.branches = [];
            step.branches.push({ value: '', next_step_id: '' });
        },

        addConditionBranch(step) {
            if (!step.branches) step.branches = [];
            step.branches.push({ field: '', operator: 'equals', value: '', next_step_id: '' });
        },

        removeStep(index) {
            if (!confirm('Remove this step?')) return;
            var removedId = this.steps[index].step_id;
            this.steps.splice(index, 1);
            this.steps.forEach(function(s) {
                if (s.next_step_id === removedId) s.next_step_id = null;
                if (s.branches) {
                    s.branches.forEach(function(b) {
                        if (b.next_step_id === removedId) b.next_step_id = '';
                    });
                }
            });
        },

        duplicateStep(index) {
            var newStep = JSON.parse(JSON.stringify(this.steps[index]));
            newStep.step_id = this.generateId();
            newStep.next_step_id = null;
            this.steps.splice(index + 1, 0, newStep);
            this.editingStep = newStep.step_id;
        },

        moveStep(index, direction) {
            var newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.steps.length) return;
            var item = this.steps.splice(index, 1)[0];
            this.steps.splice(newIndex, 0, item);
            this.steps.forEach(function(s, i) { s.sort_order = i; });
        },

        async saveFlow() {
            this.saving = true;
            this.saveMessage = '';

            var triggerConfig = {};
            var triggerType = @json($triggerType);
            if (triggerType === 'keyword') {
                triggerConfig = {
                    keywords: this.keywordsInput.split(',').map(function(k) { return k.trim(); }).filter(function(k) { return k; }),
                    match_type: this.matchType,
                };
            }

            var stepsData = this.steps.map(function(s, i) {
                var step = JSON.parse(JSON.stringify(s));
                step.sort_order = i;
                if (s.type === 'send_template' && s.config && s.config.body_params_text) {
                    step.config.body_params = s.config.body_params_text.split(',').map(function(p) { return p.trim(); });
                }
                return step;
            });

            try {
                var response = await fetch(@json($saveFlowUrl), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json($csrfToken),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        trigger_config: triggerConfig,
                        whatsapp_account_id: this.whatsappAccountId || null,
                        steps: stepsData,
                    }),
                });

                var data = await response.json();
                this.saveMessage = data.success ? '✓ Flow saved!' : '✗ ' + (data.message || 'Failed');
                this.saveError = !data.success;
            } catch (e) {
                this.saveMessage = '✗ Error: ' + e.message;
                this.saveError = true;
            }

            this.saving = false;
            var self = this;
            setTimeout(function() { self.saveMessage = ''; }, 3000);
        }
    };
}
</script>
@endpush
@endsection