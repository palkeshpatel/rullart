<footer>
    <div class="ra-footer">
        <div class="ra-footer-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-5 col-sm-6 ra-footer-logo">
                        <img src="{{ $resourceUrl }}images/logo-footer.svg" alt="Rullart">
                    </div>
                    <div class="ra-app sm-hidden">
                        <ul class="list-unstyled clearfix">
                            <li>
                                <a href="https://apps.apple.com/us/app/rullart/id1485388434" target="_blank">
                                    <img src="{{ $resourceUrl }}images/app_store.png" alt="App Store">
                                </a>
                            </li>
                            <li>
                                <a href="https://play.google.com/store/apps/details?id=com.rullart" target="_blank">
                                    <img src="{{ $resourceUrl }}images/play_store.png" alt="Play Store">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div id="ra-social" class="col-xs-7 col-sm-6 ra-social">
                        <ul class="list-unstyled clearfix">
                            <li class="lbl">
                                {{ trans('common.follow us') }}
                            </li>
                            <li>
                                <a class="whatsapp" href="https://wa.me/96594495818?text=Rullart&amp;source=website" target="_blank">
                                    <svg class="icon icon-whatsapp">
                                        <use xlink:href="/static/images/symbol-defs.svg#icon-whatsapp"></use>
                                    </svg>
                                </a>
                            </li>
                            @if(isset($settingsArr['Instagram URL']))
                                <li>
                                    <a class="instagram" href="{{ $settingsArr['Instagram URL'] }}">
                                        <svg class="icon icon-instagram">
                                            <use xlink:href="/static/images/symbol-defs.svg#icon-instagram"></use>
                                        </svg>
                                    </a>
                                </li>
                            @endif
                            @if(isset($settingsArr['Facebook URL']))
                                <li>
                                    <a class="facebook" href="{{ $settingsArr['Facebook URL'] }}">
                                        <svg class="icon icon-facebook">
                                            <use xlink:href="/static/images/symbol-defs.svg#icon-facebook"></use>
                                        </svg>
                                    </a>
                                </li>
                            @endif
                            @if(isset($settingsArr['Twitter URL']))
                                <li>
                                    <a class="twitter" href="{{ $settingsArr['Twitter URL'] }}">
                                        <svg class="icon icon-twitter">
                                            <use xlink:href="/static/images/symbol-defs.svg#icon-twitter"></use>
                                        </svg>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="ra-footer-bottom">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6 ra-footer-links">
                        <ul class="list-unstyled clearfix">
                            <li>
                                <a href="{{ route('contact', ['locale' => $locale]) }}">{{ trans('common.contact us') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('shipping', ['locale' => $locale]) }}">{{ trans('common.shipping') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('page.show', ['locale' => $locale, 'slug' => 'terms']) }}">{{ trans('common.Privacy Policy') }}</a>
                            </li>
                        </ul>
                        <p>© {{ date('Y') }} rullart. {{ trans('common.Designed & Developed by') }} <a target="_blank" href="https://www.uno-digital.com">UNO Digital</a></p>
                    </div>

                    <div class="col-sm-12 ra-app sm-visible">
                        <ul class="list-unstyled clearfix">
                            <li>
                                <a href="https://apps.apple.com/us/app/rullart/id1485388434" target="_blank">
                                    <img src="{{ $resourceUrl }}images/app_store.png" alt="App Store">
                                </a>
                            </li>
                            <li>
                                <a href="https://play.google.com/store/apps/details?id=com.rullart" target="_blank">
                                    <img src="{{ $resourceUrl }}images/play_store.png" alt="Play Store">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

