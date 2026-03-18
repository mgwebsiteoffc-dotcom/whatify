<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->getTemplates();

        foreach ($templates as $template) {
            DB::table('industry_templates')->updateOrInsert(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'slug' => $template['slug'],
                    'industry' => $template['industry'],
                    'description' => $template['description'],
                    'trigger_type' => $template['trigger_type'],
                    'trigger_config' => json_encode($template['trigger_config']),
                    'steps' => json_encode($template['steps']),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function getTemplates(): array
    {
        return [
            [
                'name' => 'Welcome Bot',
                'slug' => 'ecommerce-welcome-bot',
                'industry' => 'ecommerce',
                'description' => 'Greet new customers with welcome message and product categories',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['hi', 'hello', 'hey', 'start'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "Hello {{name}}! 👋\nWelcome to our store. How can we help you today?"], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'buttons', 'config' => ['body' => 'Choose an option:', 'buttons' => [['id' => 'products', 'title' => '🛍️ Products'], ['id' => 'orders', 'title' => '📦 My Orders'], ['id' => 'support', 'title' => '💬 Support']]], 'branches' => [['value' => 'products', 'next_step_id' => 'step_3'], ['value' => 'orders', 'next_step_id' => 'step_4'], ['value' => 'support', 'next_step_id' => 'step_5']], 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'send_message', 'config' => ['message' => "Here are our popular categories:\n\n1️⃣ New Arrivals\n2️⃣ Best Sellers\n3️⃣ Sale Items\n\nVisit our store to explore!"], 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'ask_question', 'config' => ['question' => 'Please share your order number:', 'variable_name' => 'order_number'], 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'transfer_to_agent', 'config' => ['pause_hours' => 24], 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Abandoned Cart Recovery',
                'slug' => 'ecommerce-cart-recovery',
                'industry' => 'ecommerce',
                'description' => 'Send abandoned cart reminder after Shopify cart event',
                'trigger_type' => 'shopify_abandoned_cart',
                'trigger_config' => [],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'delay', 'config' => ['value' => 1, 'unit' => 'hours'], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'send_message', 'config' => ['message' => "Hi {{name}}! 🛒\n\nYou left some items in your cart. Complete your purchase before they sell out!\n\nNeed help? Just reply here."], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'delay', 'config' => ['value' => 24, 'unit' => 'hours'], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'send_message', 'config' => ['message' => "Hey {{name}}, your cart is still waiting! 🎁\n\nUse code SAVE10 for 10% off. Hurry, offer expires soon!"], 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Order Confirmation',
                'slug' => 'ecommerce-order-confirmation',
                'industry' => 'ecommerce',
                'description' => 'Send order confirmation when Shopify order is placed',
                'trigger_type' => 'shopify_order',
                'trigger_config' => [],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "✅ Order Confirmed!\n\nHi {{name}}, your order has been placed successfully.\n\nWe'll send you tracking details once shipped.\n\nThank you for shopping with us! 🎉"], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'add_tag', 'config' => ['tag_name' => 'customer'], 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Review Request',
                'slug' => 'ecommerce-review-request',
                'industry' => 'ecommerce',
                'description' => 'Ask for product review after delivery',
                'trigger_type' => 'shopify_order',
                'trigger_config' => [],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'delay', 'config' => ['value' => 3, 'unit' => 'days'], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'send_message', 'config' => ['message' => "Hi {{name}}! 📦\n\nWe hope you received your order and are enjoying it!\n\nWould you mind leaving us a quick review? Your feedback helps us improve. ⭐"], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'buttons', 'config' => ['body' => 'Rate your experience:', 'buttons' => [['id' => 'great', 'title' => '⭐ Great!'], ['id' => 'okay', 'title' => '👍 Okay'], ['id' => 'issue', 'title' => '😟 Had Issue']]], 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Admission Inquiry Bot',
                'slug' => 'education-admission-inquiry',
                'industry' => 'education',
                'description' => 'Handle admission inquiries with course information',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['admission', 'course', 'enroll', 'fees', 'join'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "Thank you for your interest! 🎓\n\nLet me help you with admission details."], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'ask_question', 'config' => ['question' => "What's your name?", 'variable_name' => 'student_name'], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'ask_question', 'config' => ['question' => "Which course are you interested in?", 'variable_name' => 'course'], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'ask_question', 'config' => ['question' => "Please share your email:", 'variable_name' => 'student_email', 'validation_type' => 'email', 'validation_error' => 'Please enter a valid email address.'], 'next_step_id' => 'step_5', 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'send_message', 'config' => ['message' => "Thanks {{student_name}}! 📋\n\nWe've noted your interest in {{course}}.\nOur counselor will contact you shortly at {{student_email}}."], 'next_step_id' => 'step_6', 'sort_order' => 4],
                    ['step_id' => 'step_6', 'type' => 'add_tag', 'config' => ['tag_name' => 'admission_inquiry'], 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Fee Reminder',
                'slug' => 'education-fee-reminder',
                'industry' => 'education',
                'description' => 'Send fee payment reminders',
                'trigger_type' => 'api_trigger',
                'trigger_config' => ['trigger_key' => 'fee_reminder'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "Dear {{name}},\n\n📋 Fee Reminder\n\nYour fee payment is due. Please clear the dues at your earliest convenience.\n\nFor any queries, reply here or visit the accounts department."], 'sort_order' => 0],
                ],
            ],
            [
                'name' => 'Appointment Booking',
                'slug' => 'healthcare-appointment-booking',
                'industry' => 'healthcare',
                'description' => 'Book appointments via WhatsApp',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['appointment', 'book', 'doctor', 'schedule', 'checkup'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "🏥 Appointment Booking\n\nLet's schedule your appointment."], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'ask_question', 'config' => ['question' => "Patient Name:", 'variable_name' => 'patient_name'], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'ask_question', 'config' => ['question' => "Preferred Date (DD/MM/YYYY):", 'variable_name' => 'preferred_date'], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'buttons', 'config' => ['body' => 'Preferred Time Slot:', 'buttons' => [['id' => 'morning', 'title' => '🌅 Morning'], ['id' => 'afternoon', 'title' => '☀️ Afternoon'], ['id' => 'evening', 'title' => '🌙 Evening']]], 'next_step_id' => 'step_5', 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'send_message', 'config' => ['message' => "✅ Appointment Request!\n\nPatient: {{patient_name}}\nDate: {{preferred_date}}\n\nWe'll confirm shortly."], 'next_step_id' => 'step_6', 'sort_order' => 4],
                    ['step_id' => 'step_6', 'type' => 'transfer_to_agent', 'config' => ['pause_hours' => 24], 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Property Inquiry',
                'slug' => 'realestate-property-inquiry',
                'industry' => 'real_estate',
                'description' => 'Capture property inquiry leads',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['property', 'flat', 'house', 'plot', 'buy', 'rent', 'site'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "🏠 Welcome to our Property Portal!\n\nI'll help you find the perfect property."], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'buttons', 'config' => ['body' => 'What are you looking for?', 'buttons' => [['id' => 'buy', 'title' => '🏡 Buy'], ['id' => 'rent', 'title' => '🔑 Rent'], ['id' => 'invest', 'title' => '📈 Invest']]], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'ask_question', 'config' => ['question' => "Which city/area?", 'variable_name' => 'location'], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'ask_question', 'config' => ['question' => "Your budget range?", 'variable_name' => 'budget'], 'next_step_id' => 'step_5', 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'send_message', 'config' => ['message' => "Great! We have amazing properties in {{location}}.\n\nOur advisor will share listings with you shortly. 🏗️"], 'next_step_id' => 'step_6', 'sort_order' => 4],
                    ['step_id' => 'step_6', 'type' => 'add_tag', 'config' => ['tag_name' => 'property_lead'], 'next_step_id' => 'step_7', 'sort_order' => 5],
                    ['step_id' => 'step_7', 'type' => 'transfer_to_agent', 'config' => ['pause_hours' => 48], 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Menu & Order Bot',
                'slug' => 'restaurant-menu-bot',
                'industry' => 'restaurant',
                'description' => 'Share menu and take orders',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['menu', 'order', 'food', 'hungry', 'eat'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "🍽️ Welcome to our restaurant!\n\nHow can we serve you today?"], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'buttons', 'config' => ['body' => 'Choose an option:', 'buttons' => [['id' => 'menu', 'title' => '📋 View Menu'], ['id' => 'order', 'title' => '🛒 Place Order'], ['id' => 'reserve', 'title' => '📅 Reserve Table']]], 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Travel Package Inquiry',
                'slug' => 'travel-package-inquiry',
                'industry' => 'travel',
                'description' => 'Handle travel package inquiries',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['travel', 'tour', 'package', 'trip', 'holiday', 'vacation'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "✈️ Welcome to our Travel Agency!\n\nLet me help you plan your perfect trip."], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'ask_question', 'config' => ['question' => "Where would you like to travel?", 'variable_name' => 'destination'], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'ask_question', 'config' => ['question' => "How many travelers?", 'variable_name' => 'travelers', 'validation_type' => 'number'], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'ask_question', 'config' => ['question' => "Preferred travel dates?", 'variable_name' => 'dates'], 'next_step_id' => 'step_5', 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'send_message', 'config' => ['message' => "🌍 Great choice!\n\nDestination: {{destination}}\nTravelers: {{travelers}}\nDates: {{dates}}\n\nOur travel expert will share packages with you!"], 'next_step_id' => 'step_6', 'sort_order' => 4],
                    ['step_id' => 'step_6', 'type' => 'add_tag', 'config' => ['tag_name' => 'travel_lead'], 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Salon Booking',
                'slug' => 'salon-booking',
                'industry' => 'salon',
                'description' => 'Book salon appointments',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['book', 'appointment', 'haircut', 'facial', 'spa'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "💇 Welcome to our Salon!\n\nLet's book your appointment."], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'ask_question', 'config' => ['question' => "Your name:", 'variable_name' => 'client_name'], 'next_step_id' => 'step_3', 'sort_order' => 1],
                    ['step_id' => 'step_3', 'type' => 'buttons', 'config' => ['body' => 'Select service:', 'buttons' => [['id' => 'haircut', 'title' => '✂️ Haircut'], ['id' => 'facial', 'title' => '🧖 Facial'], ['id' => 'spa', 'title' => '💆 Spa']]], 'next_step_id' => 'step_4', 'sort_order' => 2],
                    ['step_id' => 'step_4', 'type' => 'ask_question', 'config' => ['question' => "Preferred date and time?", 'variable_name' => 'datetime'], 'next_step_id' => 'step_5', 'sort_order' => 3],
                    ['step_id' => 'step_5', 'type' => 'send_message', 'config' => ['message' => "✅ Booking Request!\n\nName: {{client_name}}\nTime: {{datetime}}\n\nWe'll confirm shortly!"], 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Gym Membership Inquiry',
                'slug' => 'fitness-gym-inquiry',
                'industry' => 'fitness',
                'description' => 'Handle gym membership inquiries',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['gym', 'membership', 'fitness', 'workout', 'join'], 'match_type' => 'contains'],
                'steps' => [
                    ['step_id' => 'step_1', 'type' => 'send_message', 'config' => ['message' => "💪 Welcome to our Fitness Center!\n\nReady to start your fitness journey?"], 'next_step_id' => 'step_2', 'sort_order' => 0],
                    ['step_id' => 'step_2', 'type' => 'buttons', 'config' => ['body' => 'What interests you?', 'buttons' => [['id' => 'plans', 'title' => '📋 Plans & Pricing'], ['id' => 'trial', 'title' => '🎫 Free Trial'], ['id' => 'trainer', 'title' => '🏋️ Personal Trainer']]], 'sort_order' => 1],
                ],
            ],
        ];
    }
}