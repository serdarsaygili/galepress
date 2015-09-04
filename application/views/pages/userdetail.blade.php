@layout('layouts.master')

@section('content')    
    <?php
	$UserID = 0;
	$UserTypeID = 0;
	$CustomerID = 0;
	$Username = '';
	$Password = '';
	$FirstName = '';
	$LastName = '';
	$Email = '';
	$Timezone = '';
	
	if(isset($row))
	{
		$UserID = (int)$row->UserID;
		$UserTypeID = (int)$row->UserTypeID;
		$CustomerID = (int)$row->CustomerID;
		$Username = $row->Username;
		$Password = $row->Password;
		$FirstName = $row->FirstName;
		$LastName = $row->LastName;
		$Email = $row->Email;
		$Timezone = $row->Timezone;
	}
	else 
	{
		$UserTypeID = 111;
		$CustomerID = (int)Input::get('customerID', 0);
	}
	
	$groupcodes = DB::table('GroupCode AS gc')
					->join('GroupCodeLanguage AS gcl', function($join)
					{
						$join->on('gcl.GroupCodeID', '=', 'gc.GroupCodeID');
						$join->on('gcl.LanguageID', '=', DB::raw((int)Session::get('language_id')));
					})
					->where('gc.GroupName', '=', 'UserTypes')
					->where('gc.StatusID', '=', eStatus::Active)
					->order_by('gc.DisplayOrder', 'ASC')
					->order_by('gcl.DisplayName', 'ASC')
					->get();
	
	$customers = DB::table('Customer')
					->where('StatusID', '=', eStatus::Active)
					->order_by('CustomerName', 'ASC')
					->get();

	$timezones = DB::table('Timezone')
					->order_by('TimezoneID', 'ASC')
					->get();
	?>
    <div class="col-md-8">    
        <div class="block block-drop-shadow">
          	<div class="header">
            	<h2>{{ __('common.detailpage_caption') }}</h2>
          	</div>
          	<div class="content controls">
	       		{{ Form::open(__('route.users_save'), 'POST') }}
	        		{{ Form::token() }}
				<input type="hidden" name="UserID" id="UserID" value="{{ $UserID }}" />
            	<div class="form-row">
	              	<div class="col-md-3">{{ __('common.users_usertype') }} <span class="error">*</span></div>
	              	{{ $errors->first('UserTypeID', '<p class="error">:message</p>') }}
	              	<div class="col-md-9">
	                	<select style="width: 100%;" tabindex="-1" id="UserTypeID" name="UserTypeID" class="form-control select2 required" >
							<option value=""{{ ($UserTypeID == 0 ? ' selected="selected"' : '') }}></option>
							@foreach ($groupcodes as $groupcode)
								<option value="{{ $groupcode->GroupCodeID }}"{{ ($UserTypeID == $groupcode->GroupCodeID ? ' selected="selected"' : '') }}>{{ $groupcode->DisplayName }}</option>
							@endforeach
						</select>
	            	</div>
          		</div>
	          	<div class="form-row">
		            <div class="col-md-3">{{ __('common.users_customer') }}</div>
		            <div class="col-md-9">
	              		<select class="form-control select2" style="width: 100%;" tabindex="-1" id="CustomerID" name="CustomerID">
							<option value=""{{ ($CustomerID == 0 ? ' selected="selected"' : '') }}></option>
							@foreach ($customers as $customer)
							<option value="{{ $customer->CustomerID }}"{{ ($CustomerID == $customer->CustomerID ? ' selected="selected"' : '') }}>{{ $customer->CustomerName }}</option>
							@endforeach
						</select>
		            </div>
	          	</div>                        
			    <div class="form-row">
			       <div class="col-md-3">{{ __('common.users_username') }} <span class="error">*</span></div>
			       {{ $errors->first('Username', '<p class="error">:message</p>') }}
			       <div class="col-md-9">
			        	<input type="text" name="Username" id="Username" class="form-control textbox required" value="{{ $Username }}" />
			       </div>
			    </div>
		        <div class="form-row">
		          	<div class="col-md-3">{{ __('common.users_password') }}<?php echo $UserID == 0 ? ' <span class="error">*</span>' : ''; ?></div>
		          	{{ $errors->first('Password', '<p class="error">:message</p>') }}
		          	<div class="col-md-9">
		        		<input type="password" name="Password" id="Password" class="form-control textbox<?php echo $UserID == 0 ? ' required' : ''; ?>" value="" />      
		        	</div>
		        </div>                        
		        <div class="form-row">
		          	<div class="col-md-3">{{ __('common.users_firstname') }} <span class="error">*</span></div>
		          	{{ $errors->first('FirstName', '<p class="error">:message</p>') }}
		          	<div class="col-md-9">
		            	<input type="text" name="FirstName" id="FirstName" class="form-control textbox required" value="{{ $FirstName }}" />
		          	</div>
		        </div>
		        <div class="form-row">
		          	<div class="col-md-3">{{ __('common.users_lastname') }} <span class="error">*</span></div>
		           	{{ $errors->first('LastName', '<p class="error">:message</p>') }}
		          	<div class="col-md-9">
		           		<input type="text" name="LastName" id="LastName" class="form-control textbox required" value="{{ $LastName }}" />
		          	</div>
		        </div>
	            <div class="form-row">
		          	<div class="col-md-3">{{ __('common.users_email') }} <span class="error">*</span></div>
		          	{{ $errors->first('Email', '<p class="error">:message</p>') }}
		          	<div class="col-md-9">
		            	<input type="text" name="Email" id="Email" class="form-control textbox required" value="{{ $Email }}" />
		          	</div>
	        	</div>
	        	<div class="form-row">
                    <div class="col-md-3">{{ __('common.users_timezone') }}</div>
                    <div class="col-md-9">
                        <select class="form-control select2" style="width: 100%;" tabindex="-1" id="Timezone" name="Timezone">
                            <option value=""{{ ($Timezone == '' ? ' selected="selected"' : '') }}></option>
                            @foreach ($timezones as $timezone)
                            <option value="{{ $timezone->Value }}"{{ ($Timezone == $timezone->Value ? ' selected="selected"' : '') }}>{{ $timezone->Text }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
		        <div class="form-row">
					<div class="col-md-5"></div>
	                @if($CustomerID == 0)
	                <div class="col-md-5"></div>
	                <div class="col-md-2">
	                    <input type="button" class="btn my-btn-success" name="save" value="{{ __('common.detailpage_save') }}" onclick="cUser.save();" />
	                </div>
	                @else
	                <div class="col-md-3">
	                   	<a href="#modal_default_10" data-toggle="modal"><input type="button" value="{{ __('common.detailpage_send') }}" name="send" class="btn my-btn-send" /></a>
	                </div>
	                <div class="col-md-2">
	                    <a href="#modal_default_11" data-toggle="modal"><input type="button" value="{{ __('common.detailpage_delete') }}" name="delete" class="btn delete expand remove" /></a>
	                </div>
	                <div class="col-md-2">
	                    <input type="button" class="btn my-btn-success" name="save" value="{{ __('common.detailpage_update') }}" onclick="cUser.save();" />
	                </div>
	                @endif          
		        </div>
    		{{ Form::close() }}
            </div>
        </div>
    </div>
	<div class="modal modal-info" id="modal_default_10" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Şifreyi sıfırlamak istediğinize emin misiniz?</h4>
				</div>                
				<div class="modal-footer">
					<button type="button" class="btn btn-default btn-clean" data-dismiss="modal" onclick="cUser.sendNewPassword();" style="background:#3575b1;">{{ __('common.detailpage_send') }}</button>				
					<button type="button" class="btn btn-default btn-clean" data-dismiss="modal">Vazgeç</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal modal-info" id="modal_default_11" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Silmek istediğinize emin misiniz?</h4>
				</div>                
				<div class="modal-footer">
					<button type="button" class="btn btn-default btn-clean" data-dismiss="modal" onclick="cUser.erase();" style="background:#9d0000;">{{ __('common.detailpage_delete') }}</button>				
					<button type="button" class="btn btn-default btn-clean" data-dismiss="modal">{{ __('common.contents_category_button_giveup') }}</button>
				</div>
			</div>
		</div>
	</div>
    <!-- end form_content--> 
@endsection