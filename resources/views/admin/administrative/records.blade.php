@extends('layouts.admin.master')

@section('title', 'PENRO Archiving System')

@section('content')

    <div class="overflow-auto rounded-md text-black p-4">

        <div class="w-full">
            <div class="space-x-3 mb-4">
                <x-button id="uploadBtn" label="Upload File" type="submit" style="primary" />
                <button id='add-folder-btn' data-modal-target="add-folder-modal" data-modal-toggle="add-folder-modal"
                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">
                    Create Folder
                </button>
            </div>
        </div>

        <!--call other popup here-->
        <x-modal.file-modal />



        @component('components.addfile.add-file', [
            'record' => $record ?? '',
            'isArchived' => false,
        ])
        @endcomponent


        @component('components.file-share.file-share', [
            'includePermit' => false,
        ])
        @endcomponent

        @component('components.file-request.file-request', [
            //Enter here for passing variable(future purposes)
        ])
        @endcomponent

        <div class="grid">


            @component('components.file-view', [
                'record' => $record,
                'type' => '',
                'municipality' => '',
                'isAdmin' => auth()->check() && auth()->user()->isAdmin,
                'isArchived' => false,
            ])
                <!--add something to use in the table updated by harvs-->
            @endcomponent

            <div id="mainTable" class="transition-opacity duration-500 ease-in-out opacity-100  ">
                <div class="overflow-x-auto bg-white rounded-md p-5 shadow-md border border-gray-300 h-[calc(80vh-100px)]">
                    @component('components.forms.table', [
                        'record' => $record,
                        'type' => '',
                        'municipality' => '',
                        'isAdmin' => auth()->check() && auth()->user()->isAdmin,
                        'isArchived' => false,
                    ])
                        <!--add something to use in the table updated by harvs-->
                    @endcomponent
                </div>
            </div>

            <div id="fileSection" class="transition-opacity duration-500 ease-in-out opacity-0 hidden">
                <div class="grid grid-cols-3 gap-4">
                    <div class="overflow-y-auto rounded-md bg-white p-5 border border-gray-300 shadow-md">
                        @component('components.forms.minimize-table', [
                            'type' => $type ?? '',
                            'municipality' => $municipality ?? '',
                            'record' => $record ?? '',
                            'isAdmin' => auth()->check() && auth()->user()->isAdmin,
                            'isArchived' => false,
                            'category' => $category ?? '',
                        ])
                            <!--add something to use in the table updated by harvs-->
                        @endcomponent
                    </div>

                    <div class=" p-4 col-span-2 bg-white rounded-md border border-gray-300 shadow-md">
                        @component('components.move.move-file', [
                            'type' => $type ?? '',
                            'municipality' => $municiplaity ?? '',
                            'record' => $record ?? '',
                            'isAdmin' => auth()->check() && auth()->user()->isAdmin,
                            'isArchived' => false,
                            'category' => $category ?? '',
                        ])
                        @endcomponent
                        <!--uploading files-->
                        @component('components.file-upload.file-upload', [
                            'type' => $type ?? '',
                            'municipality' => $municiplaity ?? '',
                            'record' => $record ?? '',
                            'isAdmin' => auth()->check() && auth()->user()->isAdmin,
                            'isArchived' => false,
                            'category' => $category ?? '',
                        ])
                        @endcomponent

                        @component('components.edit.edit-file', [
                            'type' => $type ?? '',
                            'municipality' => $municipality ?? '',
                            'record' => $record ?? '',
                            'includePermit' => false,
                            'authId' => Auth::user()->id,
                        ])
                        @endcomponent
                        @component('components.file-summary.file-summary', [
                            'type' => $type ?? '',
                            'municipality' => $municipality ?? '',
                            'record' => $record,
                        ])
                        @endcomponent

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
                </div>
            </div>
        </div>
    </div>
    @include('admin.file-manager.component.js')
@endsection
