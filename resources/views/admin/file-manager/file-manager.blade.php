@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')

@section('content')


                        <div class="h-[calc(90vh-100px)] rounded-md text-black p-4 bg-white shadow-md border border-300 mt-2">

                            <h1 class="font-medium  text-2xl text-gray-500">Environmental Permits and Land Records Folder</h1>

                            <h1 class="">Forestry</h1>
                            <div class="grid grid-cols-4 gap-8 mx-16 text-gray-700 font-semibold"> <!-- Use gap for consistent spacing -->
                                    <div class="flex flex-col items-center"> <!-- Flexbox for centering -->
                                    <a href="{{ route('file-manager.municipality.show', ['type' => 'tree-cutting-permits']) }}"
                                        class="text-center"> <!-- Centering text under image -->
                                        <img src="{{ asset('images/admin/folder.png') }}" alt="Tree Cutting Permits" class="w-24 mb-2">
                                        <!-- Add margin-bottom for spacing -->
                                        <h2 class="w-[120px]">Tree Cutting Permits</h2> <!-- Changed to h2 for semantic structure -->
                                    </a>
                                </div>

                                <div class="flex flex-col items-center">
                                    <a href="{{ route('file-manager.municipality.show', [
        'type' => 'tree-plantation-registration',
    ]) }}"
                                            class="text-center">
                                        <img src="{{ asset('images/admin/folder.png') }}" alt="Tree Plantation" class="w-24 mb-2">
                                        <h2 class="w-[120px]">Private Tree Plantation Registration</h2>
                                    </a>
                                </div>

                                <div class="flex flex-col items-center">
                                    <a href="{{ route('file-manager.municipality.show', ['type' => 'transport-permit']) }}" class="text-center">
                                        <img src="{{ asset('images/admin/folder.png') }}" alt="Tree Transport Permits" class="w-24 mb-2">
                                        <h2 class="w-[120px]">Tree Transport Permits</h2>
                                    </a>
                                </div>

                                <div class="flex flex-col items-center">
                                    <a href="{{ route('file-manager.municipality.show', ['type' => 'chainsaw-registration']) }}"
                                        class="text-center">
                                        <img src="{{ asset('images/admin/folder.png') }}" alt="Chainsaw Registration" class="w-24 mb-2">
                                        <h2 class="w-[120px]">Chainsaw Registration</h2>
                                    </a>
                                </div>
                            </div> 

                            <h1>Lands</h1>
                            <div class="grid grid-cols-4 gap-8 mx-16 text-gray-700 font-semibold">
                                                    <div class="flex flex-col items-center">
                                                        <a href="{{ route('file-manager.land-title.show', ['type' => 'land-title']) }}" class="text-center">
                                                            <img src="{{ asset('images/admin/folder.png') }}" alt="Land Titles/ Patented Lots" class="w-24 mb-2">
                                                            <h2 class="w-[120px]">Land Titles/ Patented Lots</h2>
                                                        </a>
                                                    </div>
                            </div>

                            <h1>Biodiversity</h1>
                            <div class="grid grid-cols-4 gap-8 mx-16 text-gray-700 font-semibold">
                                <div class="flex flex-col items-center">
                                    <a href="{{ route('file-manager.land-title.show', ['type' => 'land-title']) }}" class="text-center">
                                        <img src="{{ asset('images/admin/folder.png') }}" alt="Land Titles/ Patented Lots" class="w-24 mb-2">
                                        <h2 class="w-[120px]">Land Titles/ Patented Lots</h2>
                                    </a>
                                </div>
                            </div>
                        </div>
@endsection
