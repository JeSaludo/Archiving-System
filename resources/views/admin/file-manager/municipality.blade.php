@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')

@section('content')



    <div class="h-[calc(90vh-100px)] rounded-md text-black p-4 bg-white shadow-md border border-300 mt-2">
        <h1 class="font-medium  text-2xl text-gray-500">Environmental Permits and Land Records Folder</h1>

        <!-- Dynamic Grid Container for JavaScript to populate -->
        <div id="municipalities-container" class="grid grid-cols-4 gap-8 m-16 text-gray-700 font-semibold">
            <!-- JavaScript will populate this container -->
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('municipalities-container');
                // Show a loading message
                container.innerHTML = '<p class="col-span-4 text-center text-gray-600">Loading municipalities...</p>';


                // Fetch municipalities for Marinduque
                fetch('https://psgc.gitlab.io/api/provinces/174000000/municipalities/')
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch municipalities.');
                        return response.json();
                    })
                    .then(municipalities => {
                        // Clear the container
                        const _type = {!! json_encode($type ?? '') !!};
                        const _category = {!! json_encode($category ?? '') !!};
                        container.innerHTML = '';

                        // Populate the municipalities dynamically
                        municipalities.forEach(municipality => {
                            const municipalityDiv = document.createElement('div');
                            municipalityDiv.classList.add('flex', 'flex-col', 'items-center', 'text-center',
                                'mx-auto');

                            const municipalityUrl =
                                `{{ route('file-manager.table.show', ['type' => '__type__', 'municipality' => '__municipality__', 'category' => '__category__']) }}`
                                .replace('__type__', _type)
                                .replace('__municipality__', municipality.name)
                                .replace('__category__', _category ??
                                    'null'); // if category is null, use null

                            municipalityDiv.innerHTML = `
                        <a href="${municipalityUrl}" class="text-center">
                            <img src="{{ asset('images/admin/folder.png') }}" alt="${municipality.name} Folder" class="w-24 mb-2">
                            <h2 class="w-[120px] truncate">${municipality.name}</h2>
                        </a>
                    `;

                            container.appendChild(municipalityDiv);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching municipalities:', error);
                        container.innerHTML =
                            '<p class="col-span-4 text-center text-red-600">Error loading municipalities. Please try again later.</p>';
                    });
            });
        </script>



    </div>




@endsection
