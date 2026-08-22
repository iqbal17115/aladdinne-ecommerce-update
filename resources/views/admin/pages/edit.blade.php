@extends('layouts.app')

@section('header-title', __('Edit') . ' ' . __($page->title))

@section('content')
    <div class="container-fluid mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="m-0">{{ __('Edit') }} {{ __($page->title) }}</h4>

            <a href="{{ url()->previous() }}" class="btn btn-sm btn-danger">
                <i class="fa fa-arrow-left"></i>
                {{ __('Back') }}
            </a>
        </div>

        <form action="{{ route('admin.page.update', $page->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card border-0 rounded-12">
                <div class="card-body">
                    <div>
                        <x-input name='title' id="title" type="text" placeholder="Page Name" value="{{ $page->title }}"
                            label="Page Name" :readonly="! $page->is_editable" />
                    </div>

                    <div class="mt-3">
                        <x-input name='title_ar' id="title_ar" type="text" placeholder="Arabic Page Name"
                            value="{{ old('title_ar', $page->title_ar) }}" label="Page Name (2nd Language)"
                            :readonly="! $page->is_editable" />
                    </div>

                    <div class="mt-3">
                        <label for="editor" class="fw-bold mb-2">{{ __('Content') }}</label>
                        @if ($page->is_editable)
                            @hasPermission('admin.page.generate.AI.data')
                                <button class="btn btn-sm btn-primary rounded mb-1" id="generateAi" type="button">🧠 Generate Via
                                    Ai</button>
                            @endhasPermission
                        @endif
                        <div id="editor" @if (! $page->is_editable) style="background-color: #e9ecef;" @endif>
                            {!! old('content') ?? $page->description !!}
                        </div>
                        <input type="hidden" id="description" name="content"
                            value="{{ old('content') ?? $page->description }}">
                        @error('content')
                            <p class="text text-danger m-0">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="editor_ar" class="fw-bold mb-2">{{ __('Content (2nd Language)') }}</label>
                        <div id="editor_ar" @if (! $page->is_editable) style="background-color: #e9ecef;" @endif>
                            {!! old('content_ar', $page->description_ar) !!}
                        </div>
                        <input type="hidden" id="description_ar" name="content_ar"
                            value="{{ old('content_ar', $page->description_ar) }}">
                        @error('content_ar')
                            <p class="text text-danger m-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button class="btn btn-primary px-4 py-2.5" type="submit">
                        {{ __('Save And Update') }}
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
@if (! $page->is_editable)
    @push('css')
        <style>
            .ql-toolbar {
                display: none;
            }
        </style>
    @endpush
@endif
@push('scripts')
    <script>
        correctULTagFromQuill = (str) => {
            if (str) {
                let re = /(<ol><li data-list="bullet">)(.*?)(<\/ol>)/;
                let strArr = str.split(re);

                while (
                    strArr.findIndex((ele) => ele === '<ol><li data-list="bullet">') !== -1
                ) {
                    let index = strArr.findIndex(
                        (ele) => ele === '<ol><li data-list="bullet">'
                    );
                    if (index) {
                        strArr[index] = '<ul><li data-list="bullet">';
                        let endTagIndex = strArr.findIndex((ele) => ele === "</ol>");
                        strArr[endTagIndex] = "</ul>";
                    }
                }
                return strArr.join("");
            }
            return str;
        };

        const quill = new Quill('#editor', {
            theme: 'snow',
            readOnly: {{ $page->is_editable ? 'false' : 'true' }},
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{
                        'font': []
                    }],
                    ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    [{
                        'direction': 'rtl'
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    ['link', 'image', 'video', 'formula']
                ]
            }
        });

        quill.on('text-change', function(delta, oldDelta, source) {
            document.getElementById('description').value = correctULTagFromQuill(quill.root.innerHTML);
        });

        const quillAr = new Quill('#editor_ar', {
            theme: 'snow',
            readOnly: {{ $page->is_editable ? 'false' : 'true' }},
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{
                        'font': []
                    }],
                    ['bold', 'italic', 'underline', 'strike', 'blockquote'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    [{
                        'direction': 'rtl'
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    ['link', 'image', 'video', 'formula']
                ]
            }
        });

        quillAr.on('text-change', function(delta, oldDelta, source) {
            document.getElementById('description_ar').value = correctULTagFromQuill(quillAr.root.innerHTML);
        });
    </script>

    <script>
        $(document).on('click', '#generateAi', function() {
            var title = $('#title').val();
            $('#description').val("Generating description... Please wait ⏳");
            quill.clipboard.dangerouslyPasteHTML("<p><em>Generating description... Please wait ⏳</em></p>");
            $.ajax({
                url: "{{ route('admin.page.generate.AI.data') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: title
                },
                success: function(response) {
                    $('#description').val("");
                    quill.setText("");
                    console.log(response);

                    let lastResponse = "";
                    let fullText = response;
                    let index = 0;

                    function typeStep() {
                        if (index >= fullText.length) return;
                        lastResponse += fullText[index++];
                        $('#description').val(lastResponse);
                        quill.clipboard.dangerouslyPasteHTML(lastResponse);
                        quill.setSelection(quill.getLength(), 0);
                        setTimeout(typeStep, 10); // 10ms delay per character
                    }

                    typeStep();
                },
                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        let firstError = Object.values(error.responseJSON.errors)[0][0];
                        toastr.error(firstError);
                    } else if (error.responseJSON && error.responseJSON.message) {
                        toastr.error(error.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong");
                    }
                    $('#description').val("");
                    quill.setText("");
                }
            })
        });
    </script>
@endpush
