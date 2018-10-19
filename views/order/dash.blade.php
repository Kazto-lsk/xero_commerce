{{ XeFrontend::js(asset(\Xpressengine\Plugins\XeroCommerce\Plugin::asset('assets/js/index.js')))->appendTo('body')->load() }}
<div class="title">{{ $title }}</div>
<div id="component-container">

    <dash-component
        :dashboard='{!! $dashboard !!}'
        :user='{!! \Illuminate\Support\Facades\Auth::user() !!}'
        :user-info='{!! \Xpressengine\Plugins\XeroCommerce\Models\UserInfo::by(\Illuminate\Support\Facades\Auth::id()) !!}'
        list-url="{{route('xero_commerce::order.list')}}"></dash-component>

</div>
