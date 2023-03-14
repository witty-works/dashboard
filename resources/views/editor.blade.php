<x-app-layout :pagetitle="__('content.witty_editor')">
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20">
                @include('partials.banners')
                <div class="ibarra-sub-title-h1 margin-top">
                    {{ __('content.witty_editor') }}
                </div>
                
                <div>
                    <div class="py-10">
                        <div class="w-full col-span-6 sm:col-span-4 margin-bottom">
                            {!! __('content.witty_editor_description') !!}
                        </div>

                        <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css' />
                        <link rel="stylesheet" type='text/css' href="https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_style.min.css">

                        <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js'></script> 

                        <div id="witty_editor">
                            {!! clean(Request::get('content')) !!}
                        </div>

                        <script type="text/javascript">
                            FroalaEditor.DefineIcon('copy', {
                                template: 'image',
                                SRC: @json(URL::asset('copy.png')),
                                ALT: @json(__('content.witty_editor_copy_button'))
                            });
                            FroalaEditor.RegisterCommand('copy', {
                                title: @json(__('content.witty_editor_copy_button')),
                                focus: false,
                                undo: false,
                                refreshAfterCallback: false,
                                callback: function () {
                                    let text = this.html.get();
                                    text+= @json(__('content.witty_editor_viral_copy_text'), JSON_HEX_QUOT);
                                    let type = "text/html";
                                    let blob = new Blob([text], { type });
                                    let data = [new ClipboardItem({ [type]: blob })];
                                    navigator.clipboard.write(data);
                                }
                            });

                            new FroalaEditor('#witty_editor', {
                                key: @json(config('app.froala_key')),
                                language: @json(config('app.locale')),
                                attribution: false,
                                autofocus: true,
                                documentReady: true,
                                spellcheck: false,
                                toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink', 'clearFormatting', 'undo', 'redo', 'copy', 'help'],
                                toolbarButtonsMD: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink', 'clearFormatting', 'undo', 'redo', 'copy', 'help'],
                                toolbarButtonsSM: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'insertLink', 'undo', 'redo', 'copy', 'help'],
                                toolbarButtonsXS: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'copy', 'help'],
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
