<footer class="bg-gray-900 text-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8">

            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('website.home') }}" class="flex items-center gap-2 mb-4">
                    <i class="fab fa-whatsapp text-2xl text-emerald-400"></i>
                    <span class="text-lg font-bold text-white">Whatify</span>
                </a>
                <p class="text-sm text-gray-400 mb-4">WhatsApp Business API automation platform for smart businesses.</p>
                <div class="flex gap-3">
                    <a href="#" class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-emerald-600 transition-colors">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-emerald-600 transition-colors">
                        <i class="fab fa-linkedin text-sm"></i>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-emerald-600 transition-colors">
                        <i class="fab fa-youtube text-sm"></i>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white hover:bg-emerald-600 transition-colors">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                </div>
            </div>

            {{-- Product --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Product</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('website.features') }}" class="hover:text-emerald-400 transition-colors">Features</a></li>
                    <li><a href="{{ route('website.pricing') }}" class="hover:text-emerald-400 transition-colors">Pricing</a></li>
                    <li><a href="{{ route('website.usecases') }}" class="hover:text-emerald-400 transition-colors">Use Cases</a></li>
                    <li>
                        <a href="{{ route('website.partner') }}" class="hover:text-yellow-300 transition-colors inline-flex items-center gap-1">
                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                            <span class="text-yellow-300 font-medium">Partner Program</span>
                        </a>
                    </li>
                    <li><a href="{{ route('register') }}" class="hover:text-emerald-400 transition-colors">Free Trial</a></li>
                </ul>
            </div>

            {{-- Use Cases --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Use Cases</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('website.usecases.show', 'ecommerce') }}" class="hover:text-emerald-400 transition-colors">E-Commerce</a></li>
                    <li><a href="{{ route('website.usecases.show', 'education') }}" class="hover:text-emerald-400 transition-colors">Education</a></li>
                    <li><a href="{{ route('website.usecases.show', 'healthcare') }}" class="hover:text-emerald-400 transition-colors">Healthcare</a></li>
                    <li><a href="{{ route('website.usecases.show', 'real-estate') }}" class="hover:text-emerald-400 transition-colors">Real Estate</a></li>
                    <li><a href="{{ route('website.usecases.show', 'restaurant') }}" class="hover:text-emerald-400 transition-colors">Restaurants</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Resources</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('website.blog') }}" class="hover:text-emerald-400 transition-colors">Blog</a></li>
                    <li><a href="{{ route('website.contact') }}" class="hover:text-emerald-400 transition-colors">Contact</a></li>
                    <li><a href="{{ route('website.about') }}" class="hover:text-emerald-400 transition-colors">About Us</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('website.privacy') }}" class="hover:text-emerald-400 transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('website.terms') }}" class="hover:text-emerald-400 transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('website.refund') }}" class="hover:text-emerald-400 transition-colors">Refund Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} Whatify. All rights reserved.</p>
            <p class="text-sm text-gray-500">Official WhatsApp Business Solution Provider</p>
        </div>
    </div>
</footer>