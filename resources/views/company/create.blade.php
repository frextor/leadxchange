<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre entreprise - LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl mb-4">
                <span class="text-white text-2xl font-bold">LX</span>
            </div>
            <h2 class="text-gray-800 text-xl font-semibold">LeadXchange</h2>
        </div>

        <!-- Card Container -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Rejoindre ou créer une entreprise</h1>
            <p class="text-gray-600 text-center mb-8">Recherchez votre entreprise ou créez-en une nouvelle</p>

            <!-- Error Messages -->
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800 mb-1">Erreurs de validation</h3>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
            @endif

            <form action="{{ route('company.store') }}" method="POST" id="companyForm">
                @csrf
                <input type="hidden" name="existing_company_id" id="existing_company_id" value="">

                <!-- Company Name with Autocomplete -->
                <div class="mb-4 relative">
                    <label for="company_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'entreprise
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="company_search"
                            name="name"
                            placeholder="Tapez au moins 3 lettres..."
                            value="{{ old('name') }}"
                            autocomplete="off"
                            required
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400">
                        <!-- Loading Spinner -->
                        <div id="search-loading" class="hidden absolute right-4 top-1/2 transform -translate-y-1/2">
                            <svg class="animate-spin h-5 w-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Autocomplete Results -->
                    <div id="autocomplete-results" class="hidden absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        <!-- Results will be inserted here -->
                    </div>

                    @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Selected Company Info (Hidden initially) -->
                <div id="selected-company-info" class="hidden mb-4 p-4 bg-teal-50 border border-teal-200 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-teal-900 mb-2">Entreprise sélectionnée</h3>
                            <p class="text-sm text-teal-800"><strong>Nom:</strong> <span id="info-name"></span></p>
                            <p class="text-sm text-teal-800"><strong>SIRET:</strong> <span id="info-siret"></span></p>
                            <p class="text-sm text-teal-800"><strong>Secteur:</strong> <span id="info-sector"></span></p>
                        </div>
                        <button type="button" onclick="clearSelection()" class="text-teal-600 hover:text-teal-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- New Company Fields (Hidden when existing selected) -->
                <div id="new-company-fields">
                    <!-- SIRET -->
                    <div class="mb-4">
                        <label for="siret" class="block text-sm font-medium text-gray-700 mb-2">
                            SIRET (14 chiffres)
                        </label>
                        <input
                            type="text"
                            id="siret"
                            name="siret"
                            placeholder="12345678901234"
                            value="{{ old('siret') }}"
                            maxlength="14"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400">
                        @error('siret')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sector -->
                    <div class="mb-4">
                        <label for="sector" class="block text-sm font-medium text-gray-700 mb-2">
                            Secteur d'activité
                        </label>
                        <select
                            id="sector"
                            name="sector"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700">
                            <option value="">Sélectionnez un secteur</option>
                            <option value="Technology" {{ old('sector') == 'Technology' ? 'selected' : '' }}>Technologie</option>
                            <option value="Finance" {{ old('sector') == 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="Healthcare" {{ old('sector') == 'Healthcare' ? 'selected' : '' }}>Santé</option>
                            <option value="Education" {{ old('sector') == 'Education' ? 'selected' : '' }}>Éducation</option>
                            <option value="Retail" {{ old('sector') == 'Retail' ? 'selected' : '' }}>Commerce</option>
                            <option value="Manufacturing" {{ old('sector') == 'Manufacturing' ? 'selected' : '' }}>Industrie</option>
                            <option value="Services" {{ old('sector') == 'Services' ? 'selected' : '' }}>Services</option>
                            <option value="Real Estate" {{ old('sector') == 'Real Estate' ? 'selected' : '' }}>Immobilier</option>
                            <option value="Construction" {{ old('sector') == 'Construction' ? 'selected' : '' }}>Construction</option>
                            <option value="Agriculture" {{ old('sector') == 'Agriculture' ? 'selected' : '' }}>Agriculture</option>
                            <option value="Transport" {{ old('sector') == 'Transport' ? 'selected' : '' }}>Transport</option>
                            <option value="Tourism" {{ old('sector') == 'Tourism' ? 'selected' : '' }}>Tourisme</option>
                            <option value="Other" {{ old('sector') == 'Other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('sector')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Website (Optional) -->
                    <div class="mb-6">
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                            Site web <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <input
                            type="url"
                            id="website"
                            name="website"
                            placeholder="https://example.com"
                            value="{{ old('website') }}"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all text-gray-700 placeholder-gray-400">
                        @error('website')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex-1 bg-gray-100 text-gray-700 text-center font-semibold py-4 rounded-xl transition-all duration-300 hover:bg-gray-200 border border-gray-200">
                        Plus tard
                    </a>
                    <button
                        type="submit"
                        id="submit-btn"
                        class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold py-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl">
                        <span id="submit-text">Créer</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <p>Tapez au moins 3 lettres pour rechercher une entreprise existante</p>
        </div>
    </div>

    <script>
        let searchTimeout;
        const searchInput = document.getElementById('company_search');
        const resultsContainer = document.getElementById('autocomplete-results');
        const loadingSpinner = document.getElementById('search-loading');
        const selectedInfo = document.getElementById('selected-company-info');
        const newFields = document.getElementById('new-company-fields');
        const existingIdInput = document.getElementById('existing_company_id');
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');

        // Auto-format SIRET (only numbers)
        document.getElementById('siret').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Search companies on input
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Hide results if less than 3 characters
            if (query.length < 3) {
                resultsContainer.classList.add('hidden');
                loadingSpinner.classList.add('hidden');
                return;
            }

            // Show loading
            loadingSpinner.classList.remove('hidden');

            // Debounce search
            searchTimeout = setTimeout(() => {
                searchCompanies(query);
            }, 300);
        });

        // Search companies via AJAX
        function searchCompanies(query) {
            fetch(`{{ route('company.search') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingSpinner.classList.add('hidden');
                    displayResults(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingSpinner.classList.add('hidden');
                });
        }

        // Display search results
        function displayResults(companies) {
            if (companies.length === 0) {
                resultsContainer.innerHTML = '<div class="p-4 text-sm text-gray-500 text-center">Aucune entreprise trouvée. Vous pouvez en créer une nouvelle.</div>';
                resultsContainer.classList.remove('hidden');
                return;
            }

            let html = '<div class="py-2">';
            companies.forEach(company => {
                html += `
                    <div onclick='selectCompany(${JSON.stringify(company)})' class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0">
                        <p class="font-semibold text-gray-900">${company.name}</p>
                        <p class="text-xs text-gray-600">SIRET: ${company.siret} • ${company.sector}</p>
                    </div>
                `;
            });
            html += '</div>';

            resultsContainer.innerHTML = html;
            resultsContainer.classList.remove('hidden');
        }

        // Select existing company
        function selectCompany(company) {
            // Fill the input
            searchInput.value = company.name;

            // Set hidden field
            existingIdInput.value = company.id;

            // Display selected company info
            document.getElementById('info-name').textContent = company.name;
            document.getElementById('info-siret').textContent = company.siret;
            document.getElementById('info-sector').textContent = company.sector;
            selectedInfo.classList.remove('hidden');

            // Hide new company fields
            newFields.classList.add('hidden');

            // Make fields not required
            document.getElementById('siret').removeAttribute('required');
            document.getElementById('sector').removeAttribute('required');

            // Update button text
            submitText.textContent = 'Rejoindre';

            // Hide results
            resultsContainer.classList.add('hidden');
        }

        // Clear selection
        function clearSelection() {
            existingIdInput.value = '';
            selectedInfo.classList.add('hidden');
            newFields.classList.remove('hidden');
            searchInput.value = '';
            searchInput.focus();

            // Make fields required again
            document.getElementById('siret').setAttribute('required', 'required');
            document.getElementById('sector').setAttribute('required', 'required');

            // Update button text
            submitText.textContent = 'Créer';
        }

        // Close results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    </script>

</body>

</html>