@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



    <div class="">
        <h1 class="pl-8 font-bold text-2xl">Dashboard</h1>
        <div class="p-4  rounded-lg dark:border-gray-700">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-white border  p-4 rounded-lg shadow-md flex flex-col items-center">
                    <img src="{{ asset('images/image.svg') }}" alt="Images" class="h-16 mb-2">
                    <h2 class="text-lg font-semibold">Images</h2>
                    <p class="text-xl font-bold" id="image-count">0</p> <!-- Placeholder for image count -->
                </div>

                <div class="bg-white border  p-4 rounded-lg shadow-md flex flex-col items-center">
                    <img src="{{ asset('images/pdf.svg') }}" alt="PDFs" class="h-16 mb-2">
                    <h2 class="text-lg font-semibold">PDFs</h2>
                    <p class="text-xl font-bold" id="pdf-count">0</p> <!-- Placeholder for PDF count -->
                </div>

                <div class="bg-white border  p-4 rounded-lg shadow-md flex flex-col items-center">
                    <img src="{{ asset('images/zip.svg') }}" alt="ZIP Files" class="h-16 mb-2">
                    <h2 class="text-lg font-semibold">ZIP Files</h2>
                    <p class="text-xl font-bold" id="zip-count">0</p> <!-- Placeholder for ZIP count -->
                </div>
            </div>

            <div class="flex items-center   mb-4 rounded">
                <div class="grid grid-cols-3 gap-4"> <!-- Three-column grid layout for flexible sizing -->
                    <!-- Storage chart spans 1 column -->
                    <div class="h-full flex gap-2 col-span-2">
                        <x-storage-chart />
                        <x-areaChart />
                    </div>
                    {{-- <div class=" col-span-1 relative overflow-x-auto shadow-md sm:rounded-lg">

                    </div> --}}

                </div>


            </div>



        </div>
    </div>
    </div>
    <script>
        // Fetch function to get the count of files by extension
        fetch("/files/count")
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok " + response.statusText);
                }
                return response.json(); // Parse JSON from the response
            })
            .then(data => {
                // Update the counts in the HTML
                document.getElementById("image-count").innerText = data.image_files || 0;

                document.getElementById("pdf-count").innerText = data.pdf_files || 0;
                document.getElementById("zip-count").innerText = data.zip_files || 0;

                // Optionally log the data
                console.log(data);
            })
            .catch(error => {
                console.error("There was a problem with the fetch operation:", error);
            });
    </script>


@endsection
