<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class IndustryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->getTemplates();

        foreach ($templates as $template) {
            \App\Models\Automation::create([
                'user_id' => 0, // system templates, cloned on use
                'name' => $template['name'],
                'description' => $template['description'],
                'trigger_type' => $template['trigger_type'],
                'trigger_config' => $template['trigger_config'],
                'status' => 'draft',
                'flow_data' => $template['steps'],
            ]);
        }
    }

    protected function getTemplates(): array
    {
        return [
            // E-commerce
            [
                'name' => 'Welcome Bot (E-commerce)',
                'description' => 'Greet new customers with welcome message and product categories',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['hi', 'hello', 'hey', 'start'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "Hello {{name}}! 👋\nWelcome to our store. How can we help you today?"]],
                    ['type' => 'buttons', 'config' => ['body' => 'Choose an option:', 'buttons' => [
                        ['id' => 'products', 'title' => '🛍️ Products'],
                        ['id' => 'orders', 'title' => '📦 My Orders'],
                        ['id' => 'support', 'title' => '💬 Support'],
                    ]]],
                ],
            ],
            [
                'name' => 'Cart Recovery (E-commerce)',
                'description' => 'Send abandoned cart reminder after Shopify cart event',
                'trigger_type' => 'shopify_abandoned_cart',
                'trigger_config' => [],
                'steps' => [
                    ['type' => 'delay', 'config' => ['value' => 1, 'unit' => 'hours']],
                    ['type' => 'send_message', 'config' => ['message' => "Hi {{name}}! 🛒\n\nYou left some items in your cart. Complete your purchase before they sell out!\n\nNeed help? Just reply here."]],
                ],
            ],
            [
                'name' => 'Order Confirmation (E-commerce)',
                'description' => 'Send order confirmation when Shopify order is placed',
                'trigger_type' => 'shopify_order',
                'trigger_config' => [],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "✅ Order Confirmed!\n\nHi {{name}}, your order has been placed successfully.\n\nWe'll send you tracking details once shipped.\n\nThank you for shopping with us! 🎉"]],
                    ['type' => 'add_tag', 'config' => ['tag_name' => 'customer']],
                ],
            ],
            // Education
            [
                'name' => 'Admission Inquiry (Education)',
                'description' => 'Handle admission inquiries with course information',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['admission', 'course', 'enroll', 'fees'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "Thank you for your interest! 🎓\n\nLet me help you with admission details."]],
                    ['type' => 'ask_question', 'config' => ['question' => "What's your name?", 'variable_name' => 'student_name']],
                    ['type' => 'ask_question', 'config' => ['question' => "Which course are you interested in?", 'variable_name' => 'course']],
                    ['type' => 'ask_question', 'config' => ['question' => "Please share your email for details:", 'variable_name' => 'student_email', 'validation_type' => 'email']],
                    ['type' => 'send_message', 'config' => ['message' => "Thanks {{student_name}}! 📋\n\nWe've noted your interest in {{course}}.\n\nOur counselor will contact you shortly at {{student_email}}."]],
                    ['type' => 'add_tag', 'config' => ['tag_name' => 'admission_inquiry']],
                ],
            ],
            // Healthcare
            [
                'name' => 'Appointment Booking (Healthcare)',
                'description' => 'Book appointments via WhatsApp',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['appointment', 'book', 'doctor', 'schedule'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "🏥 Appointment Booking\n\nLet's schedule your appointment."]],
                    ['type' => 'ask_question', 'config' => ['question' => "Patient Name:", 'variable_name' => 'patient_name']],
                    ['type' => 'ask_question', 'config' => ['question' => "Preferred Date (DD/MM/YYYY):", 'variable_name' => 'preferred_date']],
                    ['type' => 'ask_question', 'config' => ['question' => "Preferred Time Slot:\n1. Morning (9AM-12PM)\n2. Afternoon (2PM-5PM)\n3. Evening (6PM-8PM)", 'variable_name' => 'time_slot']],
                    ['type' => 'send_message', 'config' => ['message' => "✅ Appointment Request Received!\n\nPatient: {{patient_name}}\nDate: {{preferred_date}}\nSlot: {{time_slot}}\n\nWe'll confirm your appointment shortly."]],
                    ['type' => 'transfer_to_agent', 'config' => ['pause_hours' => 24]],
                ],
            ],
            // Real Estate
            [
                'name' => 'Property Inquiry (Real Estate)',
                'description' => 'Capture property inquiry leads',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['property', 'flat', 'house', 'plot', 'buy', 'rent'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "🏠 Welcome to our Property Portal!\n\nI'll help you find the perfect property."]],
                    ['type' => 'buttons', 'config' => ['body' => 'What are you looking for?', 'buttons' => [
                        ['id' => 'buy', 'title' => '🏡 Buy'],
                        ['id' => 'rent', 'title' => '🔑 Rent'],
                        ['id' => 'invest', 'title' => '📈 Invest'],
                    ]]],
                    ['type' => 'ask_question', 'config' => ['question' => "Which city/area are you interested in?", 'variable_name' => 'location']],
                    ['type' => 'ask_question', 'config' => ['question' => "Your budget range?", 'variable_name' => 'budget']],
                    ['type' => 'send_message', 'config' => ['message' => "Great! We have amazing properties in {{location}} within your budget.\n\nOur property advisor will share listings with you shortly. 🏗️"]],
                    ['type' => 'add_tag', 'config' => ['tag_name' => 'property_lead']],
                    ['type' => 'transfer_to_agent', 'config' => ['pause_hours' => 48]],
                ],
            ],
            // Restaurant
            [
                'name' => 'Menu Bot (Restaurant)',
                'description' => 'Share menu and take orders',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['menu', 'order', 'food', 'hungry'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "🍽️ Welcome to our restaurant!\n\nHere's what we can do for you:"]],
                    ['type' => 'buttons', 'config' => ['body' => 'Choose an option:', 'buttons' => [
                        ['id' => 'menu', 'title' => '📋 View Menu'],
                        ['id' => 'order', 'title' => '🛒 Place Order'],
                        ['id' => 'reserve', 'title' => '📅 Reserve Table'],
                    ]]],
                ],
            ],
            // Travel
            [
                'name' => 'Package Inquiry (Travel)',
                'description' => 'Handle travel package inquiries',
                'trigger_type' => 'keyword',
                'trigger_config' => ['keywords' => ['travel', 'tour', 'package', 'trip', 'holiday'], 'match_type' => 'contains'],
                'steps' => [
                    ['type' => 'send_message', 'config' => ['message' => "✈️ Welcome to our Travel Agency!\n\nLet me help you plan your perfect trip."]],
                    ['type' => 'ask_question', 'config' => ['question' => "Where would you like to travel?", 'variable_name' => 'destination']],
                    ['type' => 'ask_question', 'config' => ['question' => "How many travelers?", 'variable_name' => 'travelers', 'validation_type' => 'number']],
                    ['type' => 'ask_question', 'config' => ['question' => "Preferred travel dates?", 'variable_name' => 'dates']],
                    ['type' => 'send_message', 'config' => ['message' => "🌍 Great choice!\n\nDestination: {{destination}}\nTravelers: {{travelers}}\nDates: {{dates}}\n\nOur travel expert will share the best packages with you!"]],
                    ['type' => 'add_tag', 'config' => ['tag_name' => 'travel_lead']],
                ],
            ],
        ];
    }
}