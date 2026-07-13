@push('scripts_libs')
    @php
        $translation = null;
        $language = getLocale();
        $translationFile = "vendor/libs/ckeditor/translations/{$language}.js";
        if (file_exists(public_path($translationFile))) {
            $translation = $translationFile;
        }
    @endphp
@if ($translation)
<script src="{{ asset($translationFile) }}"></script>
@endif
<script src="{{ asset('vendor/libs/ckeditor/plugins/uploadAdapterPlugin.js') }}"></script>
<script src="{{ asset('vendor/libs/ckeditor/ckeditor.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ClassicEditor === 'undefined') {
            return;
        }

        function UploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new UploadAdapter(loader);
            };
        }

        document.querySelectorAll('.ckeditor').forEach(function(element) {
            if (element.dataset.ckeditorInitialized === 'true') {
                return;
            }

            element.dataset.ckeditorInitialized = 'true';

            ClassicEditor.create(element, {
                language: window.config ? config.lang : 'en',
                extraPlugins: [UploadAdapterPlugin],
                mediaEmbed: {
                    previewsInData: true
                }
            }).catch(function(error) {
                console.error('Workspace CKEditor could not be initialized.', error);
                element.dataset.ckeditorInitialized = 'false';
            });
        });
    });
</script>
@endpush
