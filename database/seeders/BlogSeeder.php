<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'WhatsApp Marketing', 'slug' => 'whatsapp-marketing', 'description' => 'Tips and strategies for WhatsApp marketing campaigns'],
            ['name' => 'Automation', 'slug' => 'automation', 'description' => 'Chatbot and workflow automation guides'],
            ['name' => 'E-Commerce', 'slug' => 'ecommerce', 'description' => 'WhatsApp for online stores and e-commerce'],
            ['name' => 'Product Updates', 'slug' => 'product-updates', 'description' => 'Latest Whatify features and updates'],
            ['name' => 'Guides', 'slug' => 'guides', 'description' => 'Step-by-step guides and tutorials'],
        ];

        foreach ($categories as $i => $cat) {
            BlogCategory::updateOrCreate(['slug' => $cat['slug']], $cat + ['sort_order' => $i]);
        }

        $posts = [
            [
                'title' => 'Complete Guide to WhatsApp Business API in 2024',
                'category' => 'whatsapp-marketing',
                'excerpt' => 'Everything you need to know about WhatsApp Business API — what it is, how to get access, pricing, and how to use it for your business.',
                'body' => '<h2>What is WhatsApp Business API?</h2><p>WhatsApp Business API is the official solution for medium and large businesses to communicate with customers on WhatsApp at scale. Unlike the WhatsApp Business App, the API allows automation, chatbots, and integration with business tools.</p><h2>Who Should Use It?</h2><p>Any business that wants to send bulk messages, automate customer support, or integrate WhatsApp with their CRM or e-commerce platform needs the API.</p><h2>How to Get Access</h2><p>You can get WhatsApp Business API access through an official Business Solution Provider (BSP) like Whatify. The setup process takes just a few minutes.</p><h2>Pricing</h2><p>WhatsApp charges per conversation. Prices vary by country and message category (marketing, utility, authentication, service). In India, marketing messages cost approximately ₹0.90 per message.</p><h2>Getting Started with Whatify</h2><p>Sign up for a free trial at Whatify, connect your WhatsApp number, and start sending messages in minutes. No technical skills required.</p>',
                'read_time' => 8,
                'faq' => [
                    ['question' => 'Is WhatsApp Business API free?', 'answer' => 'The API itself has no monthly fee through Whatify, but you pay per message sent. Prices start from ₹0.30 per message depending on the category.'],
                    ['question' => 'Can I use my existing phone number?', 'answer' => 'Yes, you can use your existing business phone number with the API. However, it cannot be simultaneously used with the WhatsApp Business App.'],
                    ['question' => 'How long does setup take?', 'answer' => 'With Whatify, you can get started in as little as 5 minutes. Business verification by Meta may take 1-3 business days.'],
                ],
            ],
            [
                'title' => '10 WhatsApp Marketing Strategies to Boost Sales in 2024',
                'category' => 'whatsapp-marketing',
                'excerpt' => 'Discover proven WhatsApp marketing strategies that top e-commerce brands use to increase sales, improve customer retention and boost ROI.',
                'body' => '<h2>1. Welcome Message Automation</h2><p>Set up an automated welcome message for every new customer who messages your business. First impressions matter!</p><h2>2. Abandoned Cart Recovery</h2><p>Send timely reminders to customers who left items in their cart. WhatsApp has a 98% open rate, making it the perfect channel for cart recovery.</p><h2>3. Order Update Notifications</h2><p>Keep customers informed about their order status — confirmation, shipping, delivery. This builds trust and reduces support queries.</p><h2>4. Flash Sale Announcements</h2><p>Use WhatsApp broadcasts to announce flash sales and limited-time offers to your most engaged customers.</p><h2>5. Personalized Product Recommendations</h2><p>Use customer purchase history and preferences to send targeted product suggestions.</p><h2>6. Customer Feedback Collection</h2><p>After delivery, send a quick feedback request. Use buttons for easy rating.</p><h2>7. Loyalty Program Updates</h2><p>Notify customers about their loyalty points, rewards and exclusive member offers.</p><h2>8. Re-engagement Campaigns</h2><p>Target inactive customers with special offers to bring them back.</p><h2>9. COD Verification</h2><p>Reduce RTO by verifying cash-on-delivery orders through WhatsApp before shipping.</p><h2>10. Review Requests</h2><p>Ask satisfied customers to leave reviews on your website or marketplace listing.</p>',
                'read_time' => 10,
                'faq' => [
                    ['question' => 'What is the open rate for WhatsApp marketing?', 'answer' => 'WhatsApp messages have a 98% open rate compared to 20% for email, making it the most effective marketing channel.'],
                    ['question' => 'Is WhatsApp marketing legal?', 'answer' => 'Yes, as long as you have customer consent (opt-in) and use the official WhatsApp Business API. Spam and unsolicited messages are prohibited.'],
                ],
            ],
            [
                'title' => 'How to Build a WhatsApp Chatbot Without Coding',
                'category' => 'automation',
                'excerpt' => 'Step-by-step guide to building a WhatsApp chatbot using Whatify visual flow builder. No programming skills needed.',
                'body' => '<h2>Why Use a WhatsApp Chatbot?</h2><p>Chatbots can handle customer queries 24/7, qualify leads, book appointments, and process orders — all without human intervention.</p><h2>Getting Started</h2><p>In Whatify, go to Automations → Create New → Select your trigger type (e.g., Keyword Match) → Open the Flow Builder.</p><h2>Building Your First Flow</h2><p>1. Add a Welcome Message step<br>2. Add Interactive Buttons (Product Info, Order Status, Talk to Agent)<br>3. Add condition branches based on button responses<br>4. Add relevant responses for each path<br>5. End with agent transfer for complex queries</p><h2>Best Practices</h2><p>Keep messages short, use buttons for navigation, always provide an option to talk to a human, and test thoroughly before activating.</p>',
                'read_time' => 7,
                'faq' => [
                    ['question' => 'Do I need coding skills to build a chatbot?', 'answer' => 'No! Whatify provides a visual drag-and-drop flow builder. You can create complex chatbot flows by simply clicking and configuring steps.'],
                ],
            ],
            [
                'title' => 'WhatsApp for Shopify: Complete Integration Guide',
                'category' => 'ecommerce',
                'excerpt' => 'Learn how to integrate WhatsApp with your Shopify store for order notifications, abandoned cart recovery and customer support.',
                'body' => '<h2>Why Integrate WhatsApp with Shopify?</h2><p>WhatsApp integration allows you to send automated order confirmations, shipping updates, abandoned cart reminders and promotional messages to your Shopify customers.</p><h2>Setup Process</h2><p>1. Sign up on Whatify<br>2. Go to Integrations → Shopify<br>3. Enter your Shopify store domain and API token<br>4. Enable the features you want (orders, carts, shipping)<br>5. Webhooks are automatically configured</p><h2>Key Features</h2><p>Order confirmation messages, shipping tracking updates, abandoned cart recovery (30% recovery rate), COD verification, and promotional broadcasts.</p>',
                'read_time' => 6,
                'faq' => [
                    ['question' => 'Does Whatify support Shopify integration?', 'answer' => 'Yes! Whatify offers native Shopify integration with automatic webhook setup for orders, checkouts and customer events.'],
                ],
            ],
        ];

        foreach ($posts as $postData) {
            $category = BlogCategory::where('slug', $postData['category'])->first();

            BlogPost::updateOrCreate(
                ['slug' => Str::slug($postData['title'])],
                [
                    'category_id' => $category?->id,
                    'author_id' => 1,
                    'title' => $postData['title'],
                    'excerpt' => $postData['excerpt'],
                    'body' => $postData['body'],
                    'meta_title' => $postData['title'] . ' - Whatify Blog',
                    'meta_description' => $postData['excerpt'],
                    'faq' => $postData['faq'] ?? null,
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 30)),
                    'read_time' => $postData['read_time'],
                ]
            );
        }
    }
}