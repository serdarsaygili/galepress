<?php
$title = Config::get('custom.companyname');

if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Customer) {
    $customer = Auth::User()->Customer();
    $title = Auth::User()->FirstName . " " . Auth::User()->LastName;
}
?>
<!--<div class="page-navigation-panel logo"></div>-->
<div class="page-navigation-panel" style="background: url('/img/background/bt_cubs.png') left top repeat;background-color: rgba(29,29,29,1);">
    @if(Str::length($title) < 17)
    <div class="name">{{ __('common.dashboard_welcome') }}, {{ $title }}</div>
    @else
    <div class="name">{{ __('common.dashboard_welcome') }},<br /> {{ $title }}</div>
    @endif
    <div class="control"><a href="#" class="psn-control"><span class="icon-reorder" style="color:#1681bf;"></span></a></div>
</div>

<ul class="page-navigation bg-light">
    <li>
        <a href="{{URL::to(__('route.home'))}}"><span class="icon-home"></span>{{ __('common.home') }}</a>
    </li>
    <?php if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Manager): ?>
        <li>
    	<a href="#"><span class="icon-sitemap"></span> {{ __('common.menu_caption') }}</a>
    	<ul>
    	    {{ HTML::nav_link(__('route.customers'), __('common.menu_customers')) }}
    	    {{ HTML::nav_link(__('route.applications'), __('common.menu_applications')) }}
    	    {{ HTML::nav_link(__('route.contents'), __('common.menu_contents')) }}
    	    {{ HTML::nav_link(__('route.orders'), __('common.menu_orders')) }}
    	</ul>
        </li>
    <?php elseif (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Customer): ?>
        <li>
    	<a href="#"><span class="icon-dropbox"></span>{{ __('common.menu_caption_applications') }}</a>
    	<ul id="allApps">
    	    <script type="text/javascript">
    		$(document).ready(function () {
    		    var appID = $("input[name$='pplicationID']").val();
    		    if (document.location.href.indexOf("applicationID") !== -1) {
    			$(".page-navigation ul#allApps li a").each(function (index) {
    			    if (getURLParameter($(this).attr('href'), 'applicationID') == appID) {
    				$(this).attr('class', 'visited');
    				return false;
    			    }
    			});
    		    }
    		    function getURLParameter(url, name) {
    			return (RegExp(name + '=' + '(.+?)(&|$)').exec(url) || [, null])[1];
    		    }
    		    if (document.location.href.indexOf("applicationID") !== -1 && appID != "" && appID > 0) {
    			$(".page-navigation ul#allApps").prev().trigger('click');
    		    }
    		});
    	    </script>
		<?php if (Auth::User() != NULL): ?>
		    <?php foreach (Auth::User()->Customer()->Applications(eStatus::Active) as $app): ?>
	    	    <li style="width:100%;">
			    <?php echo HTML::link(__('route.contents') . '?applicationID=' . $app->ApplicationID, $app->Name, $app->sidebarClass()); ?>
	    	    </li>
		    <?php endforeach; ?>
		<?php endif; ?>
    	</ul>                               
        </li>
    <?php endif; ?>
    <li>
        <a href="#"><span class="icon-file-text-alt"></span> {{ __('common.menu_caption_reports') }}</a>
        <ul id="allReports">
	    <?php if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Manager): ?>
    	    <script type="text/javascript">
    		$(document).ready(function () {
    		    var reportUrl = window.location.href;
    		    var reportUrlParams = reportUrl.split("?");

    		    if (reportUrlParams[1] == "r=101")
    			$('ul#allReports li:eq(0) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=201")
    			$('ul#allReports li:eq(1) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=301")
    			$('ul#allReports li:eq(2) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=302")
    			$('ul#allReports li:eq(3) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1001")
    			$('ul#allReports li:eq(4) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1101")
    			$('ul#allReports li:eq(5) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1201")
    			$('ul#allReports li:eq(6) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1301")
    			$('ul#allReports li:eq(7) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1302")
    			$('ul#allReports li:eq(8) a').attr('class', 'visited');

    		    if (reportUrlParams[1] == "r=101" || reportUrlParams[1] == "r=201" || reportUrlParams[1] == "r=301" || reportUrlParams[1] == "r=302" || reportUrlParams[1] == "r=1001" || reportUrlParams[1] == "r=1101" || reportUrlParams[1] == "r=1201" || reportUrlParams[1] == "r=1301" || reportUrlParams[1] == "r=1302")
    			$(".page-navigation ul#allReports").prev().trigger('click');
    		});
    	    </script>
    	    {{ HTML::nav_link(__('route.reports').'?r=101', __('common.menu_report_101')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=201', __('common.menu_report_201')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=301', __('common.menu_report_301')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=302', __('common.menu_report_302')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1001', __('common.menu_report_1001')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1101', __('common.menu_report_1101')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1201', __('common.menu_report_1201')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1301', __('common.menu_report_1301')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1302', __('common.menu_report_1302')) }}
	    <?php endif; ?>
	    <?php if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Customer): ?>
    	    <script type="text/javascript">
    		$(document).ready(function () {
    		    var reportUrl = window.location.href;
    		    var reportUrlParams = reportUrl.split("?");

    		    if (reportUrlParams[1] == "r=301")
    			$('ul#allReports li:eq(0) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=302")
    			$('ul#allReports li:eq(1) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1001")
    			$('ul#allReports li:eq(2) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1301")
    			$('ul#allReports li:eq(3) a').attr('class', 'visited');
    		    else if (reportUrlParams[1] == "r=1302")
    			$('ul#allReports li:eq(4) a').attr('class', 'visited');

    		    if (reportUrlParams[1] == "r=301" || reportUrlParams[1] == "r=302" || reportUrlParams[1] == "r=1001" || reportUrlParams[1] == "r=1301" || reportUrlParams[1] == "r=1302") {
    			$(".page-navigation ul#allReports").prev().trigger('click');

    		    }
    		});
    	    </script>
    	    {{-- HTML::nav_link(__('route.reports').'?r=101', __('common.menu_report_101')) --}}
    	    {{-- HTML::nav_link(__('route.reports').'?r=201', __('common.menu_report_201')) --}}
    	    {{ HTML::nav_link(__('route.reports').'?r=301', __('common.menu_report_301')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=302', __('common.menu_report_302')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1001', __('common.menu_report_1001')) }}
    	    {{-- HTML::nav_link(__('route.reports').'?r=1101', __('common.menu_report_1101')) --}}
    	    {{-- HTML::nav_link(__('route.reports').'?r=1201', __('common.menu_report_1201')) --}}
    	    {{ HTML::nav_link(__('route.reports').'?r=1301', __('common.menu_report_1301')) }}
    	    {{ HTML::nav_link(__('route.reports').'?r=1302', __('common.menu_report_1302')) }}
	    <?php endif; ?>
        </ul>
    </li>
    <?php if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Manager): ?>
        <li>
    	<a href="#"><span class="icon-user"></span>Kullanıcı Ayarları</a>
    	<ul>
    	    {{ HTML::nav_link(__('route.users'), __('common.menu_users')) }}
    	    {{ HTML::nav_link(__('route.mydetail'), __('common.menu_mydetail')) }}
    	</ul>
        </li>
    <?php endif; ?>
    <?php if (Auth::User() != NULL && (int) Auth::User()->UserTypeID == eUserTypes::Customer): ?>
        <li>
    	<a href="{{URL::to(__('route.mydetail'))}}"><span class="icon-user"></span>{{ __('common.menu_mydetail') }}</a>
        </li>
        <li>
    	<a href="#"><span class="icon-cogs"></span>{{__('common.application_settings_caption_detail')}}</a>
    	<ul id="allSettings">
    	    <script type="text/javascript">
    		$(document).ready(function () {
		    var appID = $("input[name$='pplicationID']").val();
    		    if (document.location.href.indexOf("applicationID") === -1) {
    			$(".page-navigation ul#allSettings li a").each(function (index) {
			    var match = $(this).attr('href').match(/\d+/);
    			    if (match.length > 0 && match[0] === appID) {
    				$(this).attr('class', 'visited');
    				return false;
    			    }
    			});
    		    }
		    
		    if (document.location.href.indexOf("applicationID") === -1 && appID != "" && appID > 0) {
    			$(".page-navigation ul#allSettings").prev().trigger('click');
    		    }
    		});
    	    </script>
		<?php foreach (Auth::User()->Customer()->Applications(1) as $app): ?>
		    <li style="width:100%;">{{ HTML::link(route('applications_usersettings',$app->ApplicationID), $app->Name, $app->sidebarClass()) }}</li>
		<?php endforeach; ?>
    	</ul>
        </li>
        <li>
    	<a href="<?php echo Laravel\URL::to(__('route.clients')) ?>">
    	    <span class="icon-mobile-phone"></span><?php echo __('common.client_list') ?>
    	</a>
        </li>
        <li>
    	<a href="{{URL::to(__('route.shop'))}}"><span class="icon-credit-card"></span>{{ __('common.application_payment') }}</a>
        </li>
    <?php endif; ?>
</ul> 
