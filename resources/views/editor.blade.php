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

                        @if(!Auth::user() || Auth::user()->planId() !== 'witty_teams')
                        <script type="text/javascript">
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
                        </script>
                        @endif

                        <x-jet-input
                            id="witty_editor"
                            type="textarea"
                            class="mt-1 block w-full"
                            placeholder="{{ __('content.witty_editor_placeholder_text') }}"
                            :value="Request::get('content')"
                            rows="20"
                            cols="100"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
