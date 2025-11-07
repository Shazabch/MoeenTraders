@php
    $sideBarLinks = json_decode($sidenav);
@endphp


<div class="sidebar bg--dark">

    <div class="sidebar__inner">
        <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
        <div class="sidebar__logo">
            <a  class="sidebar__main-logo" href="{{ route('admin.dashboard') }}"><img src="{{ siteLogo('light') }}" alt="image"></a>
        </div>
        <div class="sidebar__menu-wrapper">
            <ul class="sidebar__menu">
                @foreach ($sideBarLinks as $key => $data)
                    @php
                        $showHeader = @$data->header && ((!@$data->submenu && permit(@$data->route_name)) || (@$data->submenu && permit(array_column($data->submenu, 'route_name'))));
                    @endphp

                    @if ($showHeader)
                        <li class="sidebar__menu-header">{{ __($data->header) }}</li>
                    @endif

                    @if (@$data->submenu)
                        @permit(array_column($data->submenu, 'route_name'))
                            <li class="sidebar-menu-item sidebar-dropdown">
                                <a class="{{ menuActive(@$data->menu_active, 3) }}" href="javascript:void(0)">
                                    <i class="menu-icon {{ @$data->icon }}"></i>
                                    <span class="menu-title">{{ __(@$data->title) }}</span>
                                    @foreach (@$data->counters ?? [] as $counter)
                                        @if ($$counter > 0)
                                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                        @break
                                    @endif
                                @endforeach
                            </a>
                            <div class="sidebar-submenu {{ menuActive(@$data->menu_active, 2) }} ">
                                <ul>
                                    @foreach ($data->submenu as $menu)
                                        @php
                                            $submenuParams = null;
                                            if (@$menu->params) {
                                                foreach ($menu->params as $submenuParamVal) {
                                                    $submenuParams[] = array_values((array) $submenuParamVal)[0];
                                                }
                                            }
                                        @endphp

                                        @permit($menu->route_name)
                                            <li class="sidebar-menu-item {{ menuActive(@$menu->menu_active) }} ">
                                                <a  class="nav-link" href="{{ route(@$menu->route_name, $submenuParams) }}">
                                                    <i class="menu-icon las la-dot-circle"></i>
                                                    <span class="menu-title">{{ __($menu->title) }}</span>
                                                    @php $counter = @$menu->counter; @endphp
                                                    @if (@$$counter)
                                                        <span class="menu-badge bg--info ms-auto">{{ @$$counter }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endpermit
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endpermit
                @else
                    @php
                        $mainParams = null;
                        if (@$data->params) {
                            foreach ($data->params as $paramVal) {
                                $mainParams[] = array_values((array) $paramVal)[0];
                            }
                        }
                    @endphp

                    @permit(@$data->route_name)
                        <li class="sidebar-menu-item {{ menuActive(@$data->menu_active) }}">
                            <a  class="nav-link " href="{{ route(@$data->route_name, $mainParams) }}">
                                <i class="menu-icon {{ $data->icon }}"></i>
                                <span class="menu-title">{{ __(@$data->title) }}</span>
                                @php $counter = @$data->counter; @endphp
                                @if (@$$counter)
                                    <span class="menu-badge bg--info ms-auto">{{ @$$counter }}</span>
                                @endif
                            </a>
                        </li>
                    @endpermit
                @endif
            @endforeach
        </ul>
    </div>
    <div class="version-info text-center text-uppercase">
        <span class="text-white">Moeeen</span>
        <span class="text--dark">Traders </span>
    </div>
</div>
</div>
<!-- sidebar end -->

@push('script')
<script>
    if ($('li').hasClass('active')) {
        $('.sidebar__menu-wrapper').animate({
            scrollTop: eval($(".active").offset().top - 320)
        }, 500);
    }
</script>
@endpush
