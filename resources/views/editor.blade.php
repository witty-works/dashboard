<x-app-layout :pagetitle="__('content.witty_editor')">
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper" id="maincontent">
            <div class="wittyworks-page lg:ml-20">
                @include('partials.banners')
                <div class="ibarra-sub-title-h1 margin-top">
                    @if(request()->get('onboarding'))
                    {{ __('content.witty_editor_try_out') }}
                    @else
                    {{ __('content.witty_editor') }}
                    @endif
                </div>
                
                <div>
                    <div class="py-10">
                        <div class="w-full col-span-6 sm:col-span-4 margin-bottom flex">
                            @if(request()->get('onboarding'))
                            {!! __('content.witty_editor_onboarding_description') !!}
                            @else
                            {!! __('content.witty_editor_description') !!}
                            @endif
                        </div>

                        <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css' />
                        <link rel="stylesheet" type='text/css' href="https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_style.min.css">

                        <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js'></script> 

                        <div id="witty_editor">
                            @if(request()->get('onboarding'))
                            {!! nl2br(__('content.witty_editor_example_text')) !!}
                            @else
                            {!! clean(Request::get('content')) !!}
                            @endif
                        </div>

                        <script type="text/javascript">
                            if (typeof ClipboardItem === 'function') {
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
                            }

                            FroalaEditor.DefineIcon('example', {
                                template: 'image',
                                SRC: @json(URL::asset('add-to-home.png')),
                                ALT: @json(__('content.witty_editor_example_button'))
                            });
                            FroalaEditor.RegisterCommand('example', {
                                title: @json(__('content.witty_editor_example_button')),
                                focus: false,
                                undo: false,
                                refreshAfterCallback: false,
                                callback: function () {
                                    text = @json(nl2br(__('content.witty_editor_example_text')), JSON_HEX_QUOT);
                                    this.html.insert(text);
                                    this.undo.saveStep();

                                    const event = new KeyboardEvent('keyup', {
                                        key: 'Enter',
                                        bubbles: true,
                                        cancelable: true
                                    });

                                    const editorElement = document.querySelector('.fr-element');
                                    editorElement.dispatchEvent(event);
                                }
                            });

                            new FroalaEditor('#witty_editor', {
                                key: @json(config('app.froala_key')),
                                language: @json(config('app.locale')),
                                attribution: false,
                                documentReady: true,
                                spellcheck: false,
                                heightMax: 800,
                                enter: FroalaEditor.ENTER_BR,
                                toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink', 'clearFormatting', 'undo', 'redo', 'copy', 'example', 'help'],
                                toolbarButtonsMD: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'insertLink', 'clearFormatting', 'undo', 'redo', 'copy', 'example', 'help'],
                                toolbarButtonsSM: ['fullscreen', 'bold', 'underline', 'strikeThrough', 'fontSize', 'insertLink', 'undo', 'redo', 'copy', 'example', 'help'],
                                toolbarButtonsXS: ['fullscreen', 'bold', 'underline', 'fontSize', 'copy', 'help'],
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
