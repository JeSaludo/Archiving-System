@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')

{{-- @section('content')
    <img src="{{ asset('images/denr-home.jpg') }}" class="fixed inset-0 bg-cover w-full h-full -z-10" alt="Background Image">
    <section class="bg-transparent p-10">
        <div class="grid max-w-screen-xl mx-auto lg:gap-8 xl:gap-8 lg:py-16 lg:grid-cols-12">

            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex">
                <img src="{{ asset('images/logo.png') }}" class="" alt="PENRO-logo">
            </div>

            <div class="mr-auto place-self-center lg:col-span-7">
                <h1 class="max-w-2xl text-white mb-4 text-5xl font-extrabold tracking-tight leading-none">
                    Welcome to Document Security and Digital Archiving System.</h1>
                <h1 class="max-w-2xl text-slate-300 mb-4 text-4xl font-extrabold tracking-tight leading-none">
                    PENRO-Boac Marinduque</h1>
                <p class="max-w-2xl mb-6 font-md text-gray-300 lg:mb-8 md:text-lg lg:text-xl">
                    Efficient document management system provides tailored solutions, enhancing workflow seamlessly</p>
                <a href="#"
                    class="transition ease-in-out delay-150 hover:scale-110  hover:-translate-y-1 inline-flex items-center justify-center px-5 py-3 mr-3 text-base font-medium text-center text-white border bg-primary-700 border-gray-300 rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-gray-100">
                    Get started
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </a>

            </div>
        </div>
    </section>
@endsection --}}

@section('content')
    <div id="table-container">
        <!-- Table container -->
        <h1>Table Content</h1>
    </div>

    <div id="section-container" class="hidden">
        <!-- Divs for each section instead of using templates -->
        <div id="upload-section" class="section hidden" role="region" aria-labelledby="section-upload-title">
            hello this is upload
        </div>

        <div id="edit-section" class="section hidden" role="region" aria-labelledby="section-edit-title">
            hello this is edit
        </div>

        <div id="summary-section" class="section hidden" role="region" aria-labelledby="section-summary-title">
            hello this is summary
        </div>

        <div id="move-section" class="section hidden" role="region" aria-labelledby="section-move-title">
            hello this is move
        </div>
    </div>

    <!-- Buttons -->
    <x-button id="uploadBtn" class="toggle-btn" data-toggle-target="upload" aria-controls="section-upload"
        aria-expanded="false" label="Upload File" type="button" style="primary" />

    <button class="toggle-btn" data-toggle-target="edit" aria-controls="section-edit" aria-expanded="false">
        Show Edit
    </button>
    <button class="toggle-btn" data-toggle-target="summary" aria-controls="section-summary" aria-expanded="false">
        Show Summary
    </button>
    <button class="toggle-btn" data-toggle-target="move" aria-controls="section-move" aria-expanded="false">
        Show Move
    </button>

    <!-- Close All Button with class instead of ID -->
    <button class="close-all-btn toggle-btn" type="button" aria-controls="section-close-all">
        Close All
    </button>

    <script>
        const sectionContainer = document.getElementById('section-container');
        const tableContainer = document.getElementById('table-container');
        const closeAllBtns = document.querySelectorAll('.close-all-btn'); // Select all close buttons by class

        // Function to toggle sections
        function toggleSection(sectionId) {
            // Hide all sections first
            sectionContainer.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));

            // Show the selected section
            const targetSection = document.getElementById(`${sectionId}-section`);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }

            sectionContainer.classList.remove('hidden'); // Show parent container
            tableContainer.classList.add('hidden'); // Hide table container

            // Update aria-expanded attributes for buttons
            document.querySelectorAll('.toggle-btn').forEach(button => {
                button.setAttribute('aria-expanded', button.dataset.toggleTarget === sectionId ? 'true' : 'false');
            });

            // If no sections are visible, hide parent container
            if (!sectionContainer.querySelector('.section:not(.hidden)')) {
                sectionContainer.classList.add('hidden');
                tableContainer.classList.remove('hidden'); // Show table container
            }
        }

        // Function to close all sections and return to table view
        function closeAllSections() {
            sectionContainer.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));
            sectionContainer.classList.add('hidden'); // Hide parent container
            tableContainer.classList.remove('hidden'); // Show table container
        }

        // Global event listener for all toggle buttons
        document.addEventListener('click', event => {
            const button = event.target.closest('.toggle-btn');
            if (button) {
                const sectionId = button.dataset.toggleTarget;
                if (button.classList.contains("close-all-btn")) {
                    closeAllSections(); // Close all sections when "Close All" button is clicked
                } else {
                    toggleSection(sectionId); // Toggle the respective section
                }
            }
        });
    </script>









    <!-- Parent container is hidden initially -->

    {{-- 
    <h1>Select Location</h1>

    <!-- Province Dropdown -->
    <label for="province">Province:</label>
    <select id="province">
        <option value="">Loading Provinces...</option>
    </select>

    <!-- Municipality Dropdown -->
    <label for="municipality">Municipality:</label>
    <select id="municipality" disabled>
        <option value="">Select a Province first</option>
    </select>

    <!-- Barangay Dropdown -->
    <label for="barangay">Barangay:</label>
    <select id="barangay" disabled>
        <option value="">Select a Municipality first</option>
    </select>

    <script>
        const provinceDropdown = document.getElementById('province');
        const municipalityDropdown = document.getElementById('municipality');
        const barangayDropdown = document.getElementById('barangay');

        // Load Provinces
        async function loadProvinces() {
            try {
                const response = await fetch('https://psgc.gitlab.io/api/provinces/');
                if (!response.ok) throw new Error('Failed to fetch provinces.');

                const provinces = await response.json();
                provinceDropdown.innerHTML = "<option value=''>Select Province</option>";

                provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.code;
                    option.textContent = province.name;
                    provinceDropdown.appendChild(option);
                });
            } catch (error) {
                console.error(error);
                provinceDropdown.innerHTML = "<option value=''>Error Loading Provinces</option>";
            }
        }

        // Load Municipalities based on selected Province
        async function loadMunicipalities(provinceCode) {
            try {
                const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/municipalities/`);
                if (!response.ok) throw new Error('Failed to fetch municipalities.');

                const municipalities = await response.json();
                municipalityDropdown.innerHTML = "<option value=''>Select Municipality</option>";

                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.code;
                    option.textContent = municipality.name;
                    municipalityDropdown.appendChild(option);
                });

                municipalityDropdown.disabled = false;
                barangayDropdown.innerHTML = "<option value=''>Select a Municipality first</option>";
                barangayDropdown.disabled = true;
            } catch (error) {
                console.error(error);
                municipalityDropdown.innerHTML = "<option value=''>Error Loading Municipalities</option>";
                municipalityDropdown.disabled = true;
            }
        }

        // Load Barangays based on selected Municipality
        async function loadBarangays(municipalityCode) {
            try {
                const response = await fetch(
                    `https://psgc.gitlab.io/api/municipalities/${municipalityCode}/barangays/`);
                if (!response.ok) throw new Error('Failed to fetch barangays.');

                const barangays = await response.json();
                barangayDropdown.innerHTML = "<option value=''>Select Barangay</option>";

                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.textContent = barangay.name;
                    barangayDropdown.appendChild(option);
                });

                barangayDropdown.disabled = false;
            } catch (error) {
                console.error(error);
                barangayDropdown.innerHTML = "<option value=''>Error Loading Barangays</option>";
                barangayDropdown.disabled = true;
            }
        }

        // Event Listeners
        provinceDropdown.addEventListener('change', () => {
            const provinceCode = provinceDropdown.value;
            if (provinceCode) {
                loadMunicipalities(provinceCode);
            } else {
                municipalityDropdown.innerHTML = "<option value=''>Select a Province first</option>";
                municipalityDropdown.disabled = true;
                barangayDropdown.innerHTML = "<option value=''>Select a Municipality first</option>";
                barangayDropdown.disabled = true;
            }
        });

        municipalityDropdown.addEventListener('change', () => {
            const municipalityCode = municipalityDropdown.value;
            if (municipalityCode) {
                loadBarangays(municipalityCode);
            } else {
                barangayDropdown.innerHTML = "<option value=''>Select a Municipality first</option>";
                barangayDropdown.disabled = true;
            }
        });

        // Initialize Provinces on Page Load
        loadProvinces();
    </script> --}}
@endsection
