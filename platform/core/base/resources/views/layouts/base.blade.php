<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ page_title()->getTitle() }}</title>

    <meta name="robots" content="noindex,follow"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (setting('admin_logo') || config('core.base.general.logo'))
        <meta property="og:image" content="{{ setting('admin_logo') ? RvMedia::getImageUrl(setting('admin_logo')) : url(config('core.base.general.logo')) }}">
    @endif
    <meta name="description" content="{{ strip_tags(trans('core/base::layouts.copyright', ['year' => now()->format('Y'), 'company' => setting('admin_title', config('core.base.general.base_name')), 'version' => get_cms_version()])) }}">
    <meta property="og:description" content="{{ strip_tags(trans('core/base::layouts.copyright', ['year' => now()->format('Y'), 'company' => setting('admin_title', config('core.base.general.base_name')), 'version' => get_cms_version()])) }}">

    @if (setting('admin_favicon') || config('core.base.general.favicon'))
        <link rel="icon shortcut" href="{{ setting('admin_favicon') ? RvMedia::getImageUrl(setting('admin_favicon'), 'thumb') : url(config('core.base.general.favicon')) }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    {!! Assets::renderHeader(['core']) !!}

    @if (BaseHelper::adminLanguageDirection() == 'rtl')
        <link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/rtl.css') }}">
    @endif

    @yield('head')

    @stack('header')

    <!-- Hide unwanted sidebar menus - Show Dashboard, Real Estate, Blog, Users -->
    <style>
        /* #cms-core-platform-administration - UNHIDDEN for Users */
        #cms-core-settings,
        #cms-core-appearance,
        #cms-core-media,
        #cms-core-page,
        #cms-core-plugins,
        /* #cms-plugins-blog - UNHIDDEN */
        #cms-plugins-contact,
        #cms-plugins-career,
        #cms-plugins-location,
        #cms-plugins-payment,
        #cms-plugins-translation,
        #cms-plugin-audit-log,
        #cms-plugin-backup {
            display: none !important;
        }
        /* Hide language flags in admin tables */
        .table th.language-header,
        .table td.language-header,
        th[data-class="language-header"],
        td.language-header,
        .language-badge-flags,
        .table-hover th:has(img[src*="flag"]),
        .table-hover td:has(img[src*="flag"]),
        th:has(.language-flag),
        td:has(.language-flag) {
            display: none !important;
        }
        /* Hide ALL language/translation related elements */
        .language-translations,
        .widget-language,
        [class*="language-translation"],
        .meta-box-wrap[id*="language"],
        .alert-info:has(a[href*="language"]),
        p:has(a[href*="translations"]),
        .text-end:has(a[href*="translations"]),
        /* Hide "Translations: Tiếng Việt" */
        .language-wrapper,
        .current-language,
        div[class*="language"],
        /* Hide "You are editing English version" */
        .note-info,
        .alert-info,
        /* Hide "Languages" sidebar box */
        .meta-box-wrap:has(h4:contains("Languages")),
        .widget:has(h4:contains("Languages")),
        #language-wrapper,
        .language-box,
        /* Hide Languages meta box in edit forms */
        #language_wrap,
        .meta-box-wrap#language_wrap,
        #select-post-language,
        #list-others-language {
            display: none !important;
        }
        /* Hide license and update warnings on dashboard */
        .alert-warning,
        .alert:has(a[href*="settings"]),
        .alert:has(a[href*="updater"]),
        .note-warning {
            display: none !important;
        }
        /* Hide Site Analytics widget on dashboard */
        #widget_analytics,
        .widget-item:has(a[href*="analytics"]),
        .widget-item:has(a[href*="botble.com"]),
        div:has(> p:contains("google analytics")),
        .portlet:has(a[href*="plugin-analytics"]) {
            display: none !important;
        }
        /* Hide Theme dropdown in top header */
        .dropdown-theme,
        .theme-mode,
        a[data-bs-toggle="dropdown"]:has(span:contains("Theme")),
        .nav-item:has(.dropdown-toggle:contains("Theme")) {
            display: none !important;
        }
    </style>
    <script>
        // Hide Languages box on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Hide by title text
            var widgets = document.querySelectorAll('.widget-title h4, .meta-box-wrap h4');
            widgets.forEach(function(el) {
                if (el.textContent.trim() === 'Languages' || el.textContent.includes('Languages')) {
                    var parent = el.closest('.widget') || el.closest('.meta-box-wrap');
                    if (parent) parent.style.display = 'none';
                }
            });
            // Hide language_wrap directly
            var langWrap = document.getElementById('language_wrap');
            if (langWrap) langWrap.style.display = 'none';
            // Hide select-post-language
            var selectLang = document.getElementById('select-post-language');
            if (selectLang) {
                var parent = selectLang.closest('.widget') || selectLang.closest('.meta-box-wrap');
                if (parent) parent.style.display = 'none';
            }
        });
    </script>
</head>
<body @if (BaseHelper::adminLanguageDirection() == 'rtl') dir="rtl" @endif class="@yield('body-class', 'page-sidebar-closed-hide-logo page-content-white page-container-bg-solid') {{ session()->get('sidebar-menu-toggle') ? 'page-sidebar-closed' : '' }}" style="@yield('body-style')">
    {!! apply_filters(BASE_FILTER_HEADER_LAYOUT_TEMPLATE, null) !!}

    @yield('page')

    @include('core/base::elements.common')

    {!! Assets::renderFooter() !!}

    @yield('javascript')

    <div id="stack-footer">
        @stack('footer')
    </div>

    {!! apply_filters(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, null) !!}
</body>
</html>
