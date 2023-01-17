<x-app-layout>
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

                        <script type="text/javascript">
                            function copyAll() {
                                return document.getElementById("witty_editor").value;
                            }

                            function copySelection() {
                                if (window.getSelection) {
                                    try {
                                        let copytext;

                                        let activeElement = document.activeElement;
                                        if (activeElement && activeElement.value) {
                                            // firefox bug https://bugzilla.mozilla.org/show_bug.cgi?id=85686
                                            return activeElement.value.substring(activeElement.selectionStart, activeElement.selectionEnd);
                                        }

                                        return window.getSelection().toString();
                                    } catch (e) {
                                    }
                                }
                            }

                            @if(!Auth::user() || Auth::user()->planId() !== 'witty_teams')
                            function addToClipboard(copytext, e) {
                                if (!copytext) {
                                    return false;
                                }

                                let clipdata = e.clipboardData || window.clipboardData;
                                if (!clipdata) {
                                    return false;
                                }

                                copytext+= "\n\n" + @json(__('content.witty_editor_viral_copy_text'));
                                clipdata.setData('Text', copytext);
                                return true;
                            }

                            function cutSelection() {
                                if (window.getSelection) {
                                    try {
                                        let copytext = copySelection();

                                        window.getSelection().deleteFromDocument();

                                        return copytext;
                                    } catch (e) {
                                    }
                                }
                            }

                            function copyListener(e) {
                                let copytext = copySelection();
                                if (addToClipboard(copytext, e)) {
                                    e.preventDefault();
                                }
                            }

                            function cutListener(e) {
                                let copytext = copySelection();
                                if (addToClipboard(copytext, e)) {
                                    cutSelection();
                                    e.preventDefault();
                                }
                            }

                            document.addEventListener("copy", copyListener);
                            document.addEventListener("cut", cutListener);
                            @endif

                            function copyButton() {
                                let copytext = copyAll();
                                copytext+= "\n\n" + @json(__('content.witty_editor_viral_copy_text'));

                                let type = "text/plain";
                                let blob = new Blob([copytext], { type });
                                let data = [new ClipboardItem({ [type]: blob })];

                                navigator.clipboard.write(data);
                            }

                            function shareButton() {
                                let copytext = copySelection();
                                if (!copytext) {
                                    copytext = copyAll();

                                }

                                let url = new URL(window.location.href);
                                url.search = '';
                                url = url.href + "?content=" + encodeURIComponent(copytext);
                                navigator.clipboard.writeText(url);
                                window.history.pushState({}, '', url);
                            }
                        </script>

                        <button onclick="copyButton()" alt="{{ __('content.witty_editor_copy_button') }}">
                            copy
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M280 64h40c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128C0 92.7 28.7 64 64 64h40 9.6C121 27.5 153.3 0 192 0s71 27.5 78.4 64H280zM64 112c-8.8 0-16 7.2-16 16V448c0 8.8 7.2 16 16 16H320c8.8 0 16-7.2 16-16V128c0-8.8-7.2-16-16-16H304v24c0 13.3-10.7 24-24 24H192 104c-13.3 0-24-10.7-24-24V112H64zm128-8a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/></svg>
                        </button>

                        <button onclick="shareButton()" alt="{{ __('content.witty_editor_share_button') }}">
                            share
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32h82.7L201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3V192c0 17.7 14.3 32 32 32s32-14.3 32-32V32c0-17.7-14.3-32-32-32H320zM80 32C35.8 32 0 67.8 0 112V432c0 44.2 35.8 80 80 80H400c44.2 0 80-35.8 80-80V320c0-17.7-14.3-32-32-32s-32 14.3-32 32V432c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16H192c17.7 0 32-14.3 32-32s-14.3-32-32-32H80z"/></svg>
                        </button>

                        <textarea
                            id="witty_editor"
                            placeholder="{{ __('content.witty_editor_placeholder_text') }}"
                            style="box-sizing: border-box; max-width: 100%;"
                            rows="20"
                            cols="100">{{ Request::get('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
