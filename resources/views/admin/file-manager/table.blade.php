@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')
<link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
@section('content')

    <div class="bg-slate-300 overflow-auto rounded-md text-black p-4">

        <div>
            <nav aria-label="Breadcrumb">
                <ol class="flex space-x-2 text-sm text-gray-600">
                    <li><a href="{{ route('file-manager.show') }}"><span class=""> File Manager </span></a></li>
                    <li><span class="text-gray-400"> &gt; </span></li>
                    <li><a>{{ ucwords(str_replace('-', ' ', $type)) }}</a></li>
                    @if (isset($category))
                        <li><span class="text-gray-400"> &gt; </span></li>
                        <li><a>{{ ucwords(str_replace('-', ' ', $category)) }}</a></li>
                    @endif
                    <li><span class="text-gray-400"> &gt; </span></li>
                    <li><a href="{{ route('file-manager.municipality.show', $type) }}">Municipality</a></li>
                    <li><span class="text-gray-400"> &gt; </span></li>
                    <li><a class="font-bold">{{ $municipality }}</a></li>
                </ol>
            </nav>

            <div class="my-4 space-x-3">
                <button class="bg-white text-gray-600 font-medium   px-2 p-1 rounded-md" id="uploadBtn">Upload
                    File</button>
                <button class="bg-white px-2 p-1 rounded-md text-gray-600 font-medium">Create a Folder</button>

            </div>
        </div>

        <x-modal.file-modal />

        <div class="grid gap-60">
            <div id="mainTable" class="transition-opacity duration-500 ease-in-out opacity-100">
                <div class="overflow-x-auto bg-white rounded-lg p-5">

                    <table id="sorting-table">
                        <tbody>

                        </tbody>
                    </table>

                    <script>
                        let dataTable; // Declare dataTable globally

                        document.addEventListener("DOMContentLoaded", function() {
                            const permitType = "{{ $type }}"; // Replace with your actual value
                            const municipality = "{{ $municipality }}"; // Replace with your actual value
                            const isArchived = false;

                            const params = {
                                type: permitType,
                                municipality: municipality,
                                report: '',
                                isArchived: false
                            };

                            // Remove empty parameters
                            const filteredParams = Object.fromEntries(
                                Object.entries(params).filter(([key, value]) => value !== '')
                            );

                            // Build the query string
                            const queryParams = new URLSearchParams(filteredParams).toString();

                            // Initial data fetch
                            fetchData();

                            // Function to fetch data and initialize or update the DataTable
                            async function fetchData() {
                                try {
                                    const response = await fetch(`/api/files?${queryParams}`);
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }

                                    const data = await response.json();
                                    const customData = {
                                        headings: [
                                            "Name",
                                            "Office Source",
                                            "Date Modified",
                                            "Modified By",
                                            "Category",
                                            "Classification",
                                            "Status",
                                            "Actions" // Add the Actions column
                                        ],
                                        data: data.data.map((file) => ({
                                            cells: [
                                                file.file_name,
                                                file.office_source,
                                                file.updated_at,
                                                file.user_name,
                                                file.category,
                                                file.classification,
                                                file.status,
                                                `@include('admin.file-manager.component.dropdown')`
                                            ],
                                            attributes: {
                                                class: "text-gray-700 text-left font-semibold hover:bg-gray-100"
                                            }
                                        })),
                                    };

                                    const dataTableElement = document.getElementById("sorting-table");
                                    if (dataTableElement && typeof simpleDatatables.DataTable !== 'undefined') {
                                        if (dataTable) {
                                            // If dataTable already exists, update it
                                            dataTable.refresh(customData);
                                        } else {
                                            // Create a new DataTable instance
                                            dataTable = new simpleDatatables.DataTable(dataTableElement, {
                                                classes: {
                                                    dropdown: "datatable-perPage flex items-center",
                                                    selector: "per-page-selector px-2 py-1 border rounded text-gray-600",
                                                    info: "datatable-info text-sm text-gray-500",
                                                },
                                                labels: {
                                                    perPage: "<span class='text-gray-500 m-3'>Rows</span>",
                                                    searchTitle: "Search through table data",
                                                },
                                                searchable: true,
                                                perPageSelect: true,
                                                sortable: true,
                                                perPage: 5, // Set the number of rows per page
                                                perPageSelect: [5, 10, 20, 50],
                                                data: customData
                                            });
                                        }

                                        // Initialize dropdowns for actions
                                        initializeDropdowns(data);
                                    }
                                } catch (error) {
                                    console.error('There was a problem with the fetch operation:', error);
                                    alert('Failed to fetch data. Please try again.');
                                }
                            }

                            // Function to create dropdowns
                            function createDropdown(fileId) {
                                const dropdownButton = document.getElementById(`dropdownLeftButton${fileId}`);
                                const dropdownElement = document.getElementById(`dropdownLeft${fileId}`);
                                if (dropdownButton && dropdownElement) {
                                    const options = {
                                        placement: 'left',
                                        triggerType: 'click',
                                        offsetSkidding: 0,
                                        offsetDistance: 0,
                                        ignoreClickOutsideClass: false,
                                    };
                                    new Dropdown(dropdownElement, dropdownButton, options);
                                }
                            }

                            // Function to initialize dropdowns for each file
                            function initializeDropdowns(data) {
                                data.data.forEach((file) => {
                                    createDropdown(file.id);
                                });
                            }

                            // Example of updating data after a CRUD operation
                            async function updateDataAfterCRUD() {
                                console.log("Updating data after CRUD operation...");

                                // Check if dataTable exists and has the destroy method
                                if (dataTable && typeof dataTable.destroy === "function") {
                                    dataTable.destroy(); // Destroy the existing DataTable instance
                                    dataTable = null; // Set dataTable to null after destruction
                                }

                                // Fetch new data
                                await fetchData(); // This function should reinitialize the DataTable
                                console.log("DataTable display has been refreshed!");
                            }

                            // Make updateDataAfterCRUD globally accessible
                            window.updateDataAfterCRUD = updateDataAfterCRUD;
                        }); // Close DOMContentLoaded function

                        // Archive file function
                        async function archiveFile(fileId) {
                            const csrfToken = document.querySelector('input[name="_token"]').value;

                            try {
                                const response = await fetch(`/api/files/archived/${fileId}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken // CSRF token for security
                                    },
                                });

                                const result = await response.json();

                                if (response.ok && result.success) {
                                    updateDataAfterCRUD(); // Refresh data after archiving
                                } else {
                                    alert('Failed to archive the file.');
                                    console.error(result.message || 'Unknown error');
                                }
                            } catch (error) {
                                console.error('Error archiving the file:', error);
                                alert('An error occurred while archiving the file.');
                            }
                        }
                    </script>




                </div>

            </div>



            <div id="fileSection" class="transition-opacity duration-500 ease-in-out opacity-0 pointer-events-none hidden">
                <div class="grid grid-cols-3 gap-4">
                    <div class="overflow-auto  rounded-lg bg-white p-5">
                        <table id="minimizeTable" class="">
                            <tbody>
                                <!-- Minimize table content goes here -->
                            </tbody>
                        </table>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const permitType = "{{ $type }}"; // Replace with your actual value
                                const municipality = "{{ $municipality }}"; // Replace with your actual value
                                const params = {
                                    type: permitType,
                                    municipality: municipality,
                                    report: '',
                                    isArchived: false
                                };

                                // Remove empty parameters
                                const filteredParams = Object.fromEntries(
                                    Object.entries(params).filter(([key, value]) => value !== '')
                                );

                                // Build the query string
                                const queryParams = new URLSearchParams(filteredParams).toString();

                                fetch(`/api/files?${queryParams}`)
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        const customData = {
                                            headings: [
                                                "Name",
                                                "Actions"
                                            ],
                                            data: data.data.map((file) => ({
                                                cells: [
                                                    file.file_name,
                                                    `<button id="miniBtn${file.id}" data-dropdown-toggle="miniDropdown${file.id}" data-dropdown-placement="left" class="inline-flex items-center p-0.5 text-sm font-medium text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" type="button">
                                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        </svg>
                                                    </button>
                                                    <div id="miniDropdown${file.id}" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow">
                                                        <ul class="py-2 text-sm text-gray-700 border border-gray-200 divide-y divide-gray-400" aria-labelledby="miniBtn${file.id}">
                                                            <li><a href="/api/files/${file.id}" class="block px-4 py-2 hover:bg-gray-100">View</a></li>
                                                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Download</a></li>
                                                            <li><button class="edit-button block px-4 py-2 hover:bg-gray-100" data-file-id="${file.id}">Edit</button></li>
                                                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Move</a></li>
                                                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Share</a></li>
                                                            <li><button class="file-summary-button block px-4 py-2 hover:bg-gray-100" data-file-id="${file.id}">File Summary</button></li> 
                                                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Archived</a></li>
                                                        </ul>
                                                    </div>`
                                                ],
                                                attributes: {
                                                    class: "text-black text-left hover:bg-gray-100"
                                                }
                                            })),
                                        };

                                        // Initialize the DataTable with options
                                        const dataTableElement = document.getElementById("minimizeTable");
                                        if (dataTableElement && typeof simpleDatatables.DataTable !== 'undefined') {
                                            const dataTable = new simpleDatatables.DataTable(dataTableElement, {
                                                searchable: false,
                                                sortable: false,
                                                paging: true, // Enable pagination
                                                perPage: 5, // Show 5 entries per page
                                                perPageSelect: false, // Disable per-page selection dropdown
                                                info: false, // Enable showing "Showing X to Y of Z entries"
                                                data: customData, // Pass the full data
                                                labels: {
                                                    perPage: "<span class='text-gray-500 m-3'>Rows</span>", // Custom text for perPage dropdown
                                                    searchTitle: "Search through table data", // Title attribute for the search input
                                                    loading: "Loading...",
                                                    info: "Showing {end} of {rows} rows" // Pagination info text
                                                },
                                            });

                                            // Initialize dropdowns for the current rows
                                            function initializeDropdowns() {
                                                data.data.forEach((file) => {
                                                    const dropdownButton = document.getElementById(`miniBtn${file.id}`);
                                                    const dropdownElement = document.getElementById(
                                                        `miniDropdown${file.id}`);

                                                    if (dropdownButton && dropdownElement) {
                                                        new Dropdown(dropdownElement, dropdownButton);
                                                    }
                                                });
                                            }

                                            // Listen to events that indicate table content updates
                                            dataTable.on("datatable.page", initializeDropdowns);
                                            dataTable.on("datatable.update", initializeDropdowns);
                                            initializeDropdowns(); // Initial call for dropdowns in the first page
                                        }
                                    })
                                    .catch(error => {
                                        console.error('There was a problem with the fetch operation:', error);
                                    });
                            });
                        </script>
                    </div>

                    <div class=" p-4 col-span-2 bg-white rounded-md ">
                        {{-- this for upload --}}
                        @include('admin.file-manager.component.upload-file')
                        {{-- this for file edit --}}
                        @include('admin.file-manager.component.edit-file')
                        @include('admin.file-manager.component.file-summary')

                        <div id="toast"
                            class="hidden fixed z-[90] right-0 bottom-0 m-8 bg-red-500 text-white p-4 rounded-lg shadow-lg transition-opacity duration-300 ">
                            <div class="flex justify-between items-center">
                                <p id="toast-message" class="mr-4"></p>
                                <button id="toast-close" class="text-white focus:outline-none hover:text-gray-200">
                                    <i class='bx bx-x bx-md'></i>
                                </button>
                            </div>
                            <div id="toast-timer" class="w-full h-1 bg-green-300 mt-2"></div>
                        </div>

                    </div>

                    <script>
                        const fileInput = document.getElementById('file-upload');
                        const fileUploadName = document.getElementById('file-upload-name');
                        const fileUploadError = document.getElementById('file-upload-error');


                        function validateFile() {
                            const file = fileInput.files[0];


                            if (fileInput.files.length === 0) {
                                fileUploadError.textContent = "Please upload a file.";
                                fileUploadError.classList.remove('invisible');
                                return false; // Validation failed
                            }


                            const allowedTypes = [
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png',
                                'application/zip',
                                'application/x-zip-compressed', // Some browsers may use this
                                'multipart/x-zip' // Occasionally used
                            ];


                            if (!allowedTypes.includes(file.type)) {
                                fileUploadError.textContent = "Invalid file type. Please upload a PDF, Word document, image, or ZIP file.";
                                fileUploadError.classList.remove('invisible');
                                return false;
                            }




                            fileUploadError.classList.add('invisible');
                            return true;

                        }

                        fileInput.addEventListener('change', function() {
                            const fileUploadError = document.getElementById('file-upload-error');

                            if (fileInput.files.length > 0) {
                                const selectedFile = fileInput.files[0];
                                fileUploadName.textContent = selectedFile.name; // Update Step 1
                                fileUploadError.classList.add('invisible'); // Hide error if file is chosen
                            } else {
                                fileUploadName.textContent = 'No file chosen'; // Reset if no file is chosen
                                fileUploadError.classList.remove('invisible'); // Show error if no file is chosen
                            }
                        });

                        function showToast(message, isSuccess) {
                            const toast = document.getElementById('toast');
                            const toastMessage = document.getElementById('toast-message');
                            const toastClose = document.getElementById('toast-close');
                            const toastTimer = document.getElementById('toast-timer');

                            toastMessage.textContent = message;
                            toast.classList.remove('hidden');


                            if (isSuccess) {
                                toast.classList.add('bg-green-500');
                                toast.classList.remove('bg-red-500');
                                toastTimer.classList.remove('bg-red-300');
                                toastTimer.classList.add('bg-green-300');
                            } else {
                                toast.classList.add('bg-red-500');
                                toast.classList.remove('bg-green-500');
                                toastTimer.classList.remove('bg-green-300');
                                toastTimer.classList.add('bg-red-300');
                            }

                            let timerDuration = 3000;
                            let timerWidth = 100;


                            toastTimer.style.width = '100%';


                            const timerInterval = setInterval(() => {
                                timerWidth -= (100 / (timerDuration / 100));
                                toastTimer.style.width = `${timerWidth}%`;
                            }, 100);


                            setTimeout(() => {
                                clearInterval(timerInterval);
                                toast.classList.add('hidden');
                            }, timerDuration);


                            toastClose.onclick = function() {
                                clearInterval(timerInterval);
                                toast.classList.add('hidden');
                            };
                        }


                        document.getElementById('upload-form').addEventListener('submit', function(e) {
                            e.preventDefault();

                            const submitButton = document.getElementById('upload-btn');
                            const buttonText = document.getElementById('button-text');
                            const buttonSpinner = document.getElementById('button-spinner');

                            submitButton.disabled = true;
                            buttonText.classList.add('hidden'); // Hide the button text
                            buttonSpinner.classList.remove('hidden');

                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('input[name="_token"]').value;

                            const officeSource = document.getElementById('office-source').value;
                            const category = document.getElementById('category').value;
                            const classification = document.getElementById('classification').value;
                            const status = document.getElementById('status').value;
                            const permit_type = document.getElementById("permit_type").value;
                            const land_category = document.getElementById("land_category").value;
                            const municipality = document.getElementById("municipality").value;


                            formData.append('office_source', officeSource);
                            formData.append('category', category);
                            formData.append('classification', classification);
                            formData.append('status', status);
                            formData.append('permit_type', permit_type);
                            formData.append('land_category', land_category);
                            formData.append('municipality', municipality);

                            let fileId;

                            fetch('/file-upload', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log(document.getElementById('name-of-client')
                                        .value);
                                    if (data.success) {
                                        showToast(data.message, true);
                                        fileId = data.fileId;

                                        let formPermit = new FormData();
                                        formPermit.append('file_id', fileId);
                                        formPermit.append('permit_type', permit_type);

                                        // Gather values based on form type
                                        if (permit_type === 'tree-cutting-permits') {
                                            formPermit.append('species', document.getElementById('species').value);
                                            formPermit.append('name_of_client', document.getElementById('name-of-client')
                                                .value);
                                            formPermit.append('number_of_trees', document.getElementById(
                                                    'no-of-tree-species')
                                                .value);
                                            formPermit.append('location', document.getElementById('location').value);
                                            formPermit.append('date_applied', document.getElementById('date-applied')
                                                .value);
                                        } else if (permit_type === 'tree-plantation') {
                                            formPermit.append('name_of_client', document.getElementById('name-of-client')
                                                .value);
                                            formPermit.append('number_of_trees', document.getElementById(
                                                'number_of_trees').value);
                                            formPermit.append('location', document.getElementById('location').value);
                                            formPermit.append('date_applied', document.getElementById('date-applied')
                                                .value);
                                        } else if (permit_type === 'tree-transport-permits') {
                                            formPermit.append('species', document.getElementById('species').value);
                                            formPermit.append('name_of_client', document.getElementById('name-of-client')
                                                .value);
                                            formPermit.append('number_of_trees', document.getElementById('number-of-trees')
                                                .value);
                                            formPermit.append('destination', document.getElementById('destination')
                                                .value);
                                            formPermit.append('date_applied', document.getElementById('date-applied')
                                                .value);
                                            formPermit.append('date_of_transport', document.getElementById(
                                                    'date-of-transport')
                                                .value);
                                        } else if (permit_type === 'chainsaw-registration') {
                                            formPermit.append('name_of_client', document.getElementById('name-of-client')
                                                .value);
                                            formPermit.append('location', document.getElementById('location').value);
                                            formPermit.append('serial_number', document.getElementById('serial-number')
                                                .value);
                                            formPermit.append('date_applied', document.getElementById('date-applied')
                                                .value);
                                        } else if (permit_type === 'land-titles') {
                                            formPermit.append('name_of_client', document.getElementById('name-of-client')
                                                .value);
                                            formPermit.append('location', document.getElementById('location').value);
                                            formPermit.append('lot_number', document.getElementById('lot-number').value);
                                            formPermit.append('property_category', document.getElementById(
                                                    'property-category')
                                                .value);
                                        }

                                        console.log(permit_type);
                                        fetch('/permit-upload', {
                                                method: 'POST',
                                                body: formPermit,
                                                headers: {
                                                    'X-CSRF-TOKEN': csrfToken
                                                }

                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    updateDataAfterCRUD();
                                                    console.log("Scueeess")
                                                }
                                            })
                                            .catch((error) => {

                                                showToast(error || 'File upload failed.', false);
                                            });



                                    } else {
                                        console.log(data);
                                        showToast(data.message || 'File upload failed.', false);
                                    }
                                })
                                .catch(error => {
                                    console.log(error);
                                }).finally(() => {

                                    this.reset();

                                    const fileInput = document.getElementById('file-upload');
                                    const fileUploadName = document.getElementById('file-upload-name');


                                    fileUploadName.textContent = 'No file chosen';


                                    submitButton.disabled = false;
                                    buttonText.classList.remove('hidden'); // Show the button text again
                                    buttonSpinner.classList.add('hidden'); // Hide the spinner

                                });
                        });
                    </script>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Function to handle fading out and in sections
        function toggleSections(showFileSection) {
            const mainTable = document.getElementById('mainTable');
            const fileSection = document.getElementById('fileSection');

            if (showFileSection) {
                // Fade out the main table
                mainTable.classList.remove('opacity-100');
                mainTable.classList.add('opacity-0');

                setTimeout(() => {
                    mainTable.classList.add('pointer-events-none', 'hidden'); // Add hidden after fade-out is done
                    // Fade in the file section
                    fileSection.classList.remove('opacity-0', 'hidden', 'pointer-events-none');
                    fileSection.classList.add('opacity-100');
                }, 300); // Match this to your CSS transition duration
            } else {
                // Fade out the file section
                fileSection.classList.remove('opacity-100');
                fileSection.classList.add('opacity-0');

                setTimeout(() => {
                    fileSection.classList.add('pointer-events-none', 'hidden'); // Add hidden after fade-out is done
                    mainTable.classList.remove('pointer-events-none', 'hidden', 'opacity-0');
                    mainTable.classList.add('opacity-100');
                }, 300); // Match this to your CSS transition duration
            }
        }

        // Helper function to show the correct div and hide others
        function toggleDivVisibility(showDivId) {
            const sections = ['upload-file-div', 'edit-file-div', 'file-summary-div'];
            sections.forEach(section => {
                const sectionDiv = document.getElementById(section);
                if (section === showDivId) {
                    sectionDiv.classList.remove('hidden');
                } else {
                    sectionDiv.classList.add('hidden');
                }
            });
        }

        // Event listener for the upload button
        document.getElementById('uploadBtn').addEventListener('click', function() {
            toggleSections(true);
            toggleDivVisibility('upload-file-div');
        });

        // Event listener for the edit button
        document.body.addEventListener('click', function(event) {
            if (event.target.matches('.edit-button')) {
                toggleSections(true);
                const fileId = event.target.dataset.fileId; // Get the file ID if needed
                console.log('Edit button clicked for file ID:', fileId);
                fetchFileData(fileId);
                toggleDivVisibility('edit-file-div');
            }
        });

        // Event listener for the file summary button
        document.body.addEventListener('click', function(event) {
            if (event.target.matches('.file-summary-button')) {
                toggleSections(true);
                const fileId = event.target.dataset.fileId; // Get the file ID from the button
                console.log('File Summary button clicked for file ID:', fileId);
                fetchFileDetails(fileId); // Call a function to fetch file summary data
                toggleDivVisibility('file-summary-div');
            }
        });

        // Event listener for the close buttons in the file section
        document.getElementById('close-upload-btn').addEventListener('click', function() {
            toggleSections(false);
        });

        document.getElementById('close-edit-btn').addEventListener('click', function() {
            toggleSections(false);
        });

        document.getElementById('close-summary-btn').addEventListener('click', function() {
            toggleSections(false);
        });
    </script>


    <script src="{{ asset('js/file-modal.js') }}"></script>
@endsection
