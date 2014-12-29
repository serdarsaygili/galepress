<?php
session_start();

$_SESSION["benimdilim"]="tr";
$uri = $_SERVER['REQUEST_URI'];
$segment = explode('/', $uri);
$_SESSION["benimdilim"]=$segment[1];

if(isset($_POST["submit_tr"]))
{
	header('Location: /tr/siparis');
}
else if(isset($_POST["submit_eng"]))
{
	header('Location: /en/order');
}
else if(isset($_POST["submit_de"]))
{
	header('Location: /de/bestellen');
}

?>
<?php
  $orderNo = '';
  $note = '';
  $product = '';
  $qty = 0;
  if(isset($_GET['orderno'])) {
    $orderNo = $_GET['orderno'];
  }

  if(isset($_GET['note']) && strlen($orderNo) == 0) {
    $note = $_GET['note'];
    preg_match("'<b>Havale No </b> : (.*?)<br>'s", $note, $matches);
    if($matches) {
      $orderNo = $matches[1];
    }
  }

  if(isset($_GET['product'])) {
    $product = $_GET['product'];
  }

  if(isset($_GET['qty'])) {
    $qty = (int)$_GET['qty'];
  }
?>
<!DOCTYPE html>
<!--[if IE 8]>			<html class="ie ie8"> <![endif]-->
<!--[if IE 9]>			<html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->	<html> <!--<![endif]-->
<head>
	<?php include('language.php'); ?>
	<!-- Basic -->
	<meta charset="utf-8">
	<title><?php echo dil_SITE_TITLE ?></title>
	<meta name="keywords" content="GalePress Dijital Yayıncılık" />
	<meta name="description" content="GalePress - Dijital Yayıncılık Platformu">
	<meta name="author" content="galepress.com">

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="/website/vendor/jquery.js"><\/script>')</script>
  <script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="/uploadify/jquery.uploadify-3.1.min.js"></script>
	<script src="/bundles/jupload/js/jquery.iframe-transport.js"></script>
	<script src="/bundles/jupload/js/jquery.fileupload.js"></script>
	<script src="/js/jquery.base64.decode.js"></script>
	<script src="/js/gurus.string.js"></script>

    <!-- Bootstrap core CSS -->
  <link href="/website/app-form/css/bootstrap.css" rel="stylesheet">

  <!-- Add custom CSS here -->
  <link href="/website/app-form/css/sb-admin.css" rel="stylesheet">
  <link rel="stylesheet" href="/website/app-form/font-awesome/css/font-awesome.min.css">

<style type="text/css">
.stageSuccess{
  background: #5FCA17;
}
.navbar-inverse .navbar-nav > li.disabled a{
  color:#999 !important;
}
.headerPattern{

  background: url(/website/app-form/images/pattern.jpg) repeat;
  background-color: white;
}
.fileUpload {
  position: relative;
  overflow: hidden;
  margin: 10px;
  margin-top: -1px;
  width: 150px;
  height: 100px;
}
.fileUpload input.upload {
  position: absolute;
  top: 0;
  right: 0;
  margin: 0;
  padding: 0;
  font-size: 20px;
  cursor: pointer;
  opacity: 0;
  filter: alpha(opacity=0);
  width: 150px;
  height: 100px;
}
.noWhiteSpace{
  white-space: normal;
}
.appListSuccess{
  color: #464242 !important;
  font-weight: 700 !important;
}
.disabled{
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  pointer-events: none;
}
.ulDisabled{
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  pointer-events: none;
}
.liBorders{
  border-left: 3px solid rgba(0,0,0,0.1);
  left: 0;
  top: 0;
  position: absolute;
  height: 100%;
}
.liBordersActive{
  border-left: 4px solid #428bca;
  left: 0;
  top: 0;
  position: absolute;
  height: 100%;
}
.active{
  background: rgb(238, 238, 238);
}
::-webkit-input-placeholder {
  color: rgba(0,0,0,.32) !important;
  font-size: 12px !important;
  font-style: italic;
}

:-moz-placeholder { /* Firefox 18- */
  color: rgba(0,0,0,.32) !important;
  font-size: 12px !important;
  font-style: italic;
}

::-moz-placeholder {  /* Firefox 19+ */
  color: rgba(0,0,0,.32) !important;
  font-size: 12px !important;
  font-style: italic;
}

:-ms-input-placeholder {  
  color: rgba(0,0,0,.32) !important;
  font-size: 12px !important;
  font-style: italic;
}
fieldset.scheduler-border {
  border: 2px dashed #e5e5e5 !important;
  padding: 0 1.4em 1.4em 1.4em !important;
  margin: 0 0 1.5em 0 !important;
  -webkit-box-shadow:  0px 0px 0px 0px #e5e5e5;
  box-shadow:  0px 0px 0px 0px #e5e5e5;
}

legend.scheduler-border {
  font-size: 1.2em !important;
  font-weight: bold !important;
  text-align: left !important;

}
input[type=button]
{
    outline: 0 !important;
}

</style>
	<?php 
	$a = include($_SERVER['DOCUMENT_ROOT']."/../application/language/".$_SESSION["benimdilim"]."/route.php");
	?>
	<script type="text/javascript">
		<!--
		var route = new Array();
		route["orders_save"] = "<?php echo $a['orders_save']; ?>";
		route["orders_uploadfile"] = "<?php echo $a['orders_uploadfile']; ?>";
		route["orders_uploadfile2"] = "<?php echo $a['orders_uploadfile2']; ?>";
		// -->
	</script>
</head>
<body>
  <input type="hidden" id="currentlanguage" value="<?php echo $_SESSION["benimdilim"] ?>" />
  <div id="wrapper">

    <!-- Sidebar -->
    <nav class="navbar navbar-inverse navbar-fixed-top headerPattern" role="navigation">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Navigasyon</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <h3 style="margin-left:10px;">Uygulama Oluşturma Formu |<small> <span id="detailStage">Uygulama Detaylarını Gir</span></small></h3>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav ulDisabled">
          <li class="active"><a href="#" id="firstListItem" style="line-height: 15px;"><span class="liBordersActive"></span> Uygulama Detayı<i class="fa fa-check-circle pull-right" style="opacity:0.3; padding:1px"></i></a></li>
          <li class="disabled"><a href="#" id="secondListItem" style="line-height: 15px;"><span class="liBorders"></span> Uygulama Resimleri<i class="fa fa-check-circle pull-right" style="opacity:0.3; padding:1px"></i></a></li>
          <li class="disabled"><a href="#" id="thirdListItem" style="line-height: 15px;"><span class="liBorders"></span> Uygulamayı Oluştur<i class="fa fa-check-circle pull-right" style="opacity:0.3; padding:1px"></i></a></li>
      </div><!-- /.navbar-collapse -->
    </nav>

    <div id="page-wrapper">
      <div class="row">
        <div class="col-lg-6">
          <form role="form">
            <div id="stage1">
              <div class="pull-right"><span style="color: #428bca;font-size: 17px;font-family: monospace; font-weight:bold">*</span><span style="font-size: 13px;font-style: italic; color: rgb(165, 165, 165);">  zorunlu alanları ifade eder.</span></div>
              <hr style="margin-top:3px; margin-bottom:10px; clear:both;">
              
              <div class="form-group">
                <label>Sipariş No <span style="color: #428bca;font-size: 17px;font-family: monospace;">*</span></label>
                <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="OrderNoPopup" data-toggle="popover" title="<?php echo dil_SIPARIS_NO ?>" data-content="<?php echo dil_APP_ORDER_NO_TIP?>" class="fa fa-info-circle"></i>
                <input type="hidden" name="Product" id="Product" value="<?php echo $product; ?>">
                <input type="hidden" name="Qty" id="Qty" value="<?php echo $qty; ?>">
                <input type="text" placeholder="Your Order No" name="OrderNo" id="OrderNo" maxlength="50" class="form-control" value="<?php echo $orderNo; ?>" required>
              </div>

              <div class="form-group">
                <label>Uygulama Adı <span style="color: #428bca;font-size: 17px;font-family: monospace;">*</span></label>
                <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="AppNamePopup" data-toggle="popover" title="<?php echo dil_UYGULAMAAD ?>" data-content="<?php echo dil_UYGULAMAAD_TIP ?>" class="fa fa-info-circle"></i>
                <input type="text" id="Name" placeholder="Your Application Name" name="Name" maxlength="14" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label>Uygulama Açıklaması <span style="color: #428bca;font-size: 17px;font-family: monospace;">*</span></label>
                <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="AppDescPopup" data-toggle="popover" title="<?php echo dil_UYGULAMAACIKLAMA ?>" data-content="<?php echo dil_APP_DESC_TIP?>" class="fa fa-info-circle"></i>
                <textarea id="Description" name="Description" placeholder="GalePress; dergi, gazete, katalog vb. materyallerinizi, kod bilgisi gerektirmeden kolayca dijital ortamda okuyucularınız ile buluşmanızı sağlayan yayın platformudur." maxlength="4000" rows="3" class="form-control" required></textarea>
              </div>
           
              <div class="form-group">
                <label>Anahtar Kelimeler <span style="color: #428bca;font-size: 17px;font-family: monospace;">*</span></label>
                <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="KeywordsPopup" data-toggle="popover" title="<?php echo dil_KEYWORDS ?>" data-content="<?php echo dil_UYGULAMAKEYS_TIP ?>" class="fa fa-info-circle"></i>
                <textarea id="Keywords" placeholder="GalePress, Dijital Yayıncılık, Digital Publishing, İnteraktif Pdf, Mobil Uygulama, Detaysoft" name="Keywords" maxlength="100" rows="2" class="form-control" required></textarea>
              </div>
              <hr style="margin-bottom:10px;margin-top:0px;">

	            <div class="row">
	                <div class="col-lg-6">

	                    <div class="form-group">
	                      <label>Email</label>
                        <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="EmailPopup" data-toggle="popover" title="Email" data-content="<?php echo dil_EMAIL_TIP ?>" class="fa fa-info-circle"></i>
	                      <input id="Email" type="email" placeholder="youremail@youradress.com" name="Email" maxlength="50" class="form-control">
	                    </div>

	                    <div class="form-group">
	                      <label>Web Site</label>
                        <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="WebSitePopup" data-toggle="popover" title="Web Site" data-content="Uygulamanız içerisinde web sayfanızı görüntüleyebilirsiniz." class="fa fa-info-circle"></i>
	                      <input id="Website" type="text" name="Website" placeholder="http://www.yoursite.com" maxlength="50" class="form-control">
	                    </div>

	                </div>
	                <div class="col-lg-6">
	                  <div class="form-group">
	                    <label>Facebook</label>
                      <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="FacePopup" data-toggle="popover" title="Facebook" data-content="Uygulamanız içerisinde facebook sayfanızı görüntüleyebilirsiniz." class="fa fa-info-circle"></i>
	                    <input id="Facebook" type="text" placeholder="http://facebook.com/YourPage" name="Facebook" maxlength="50" class="form-control">
	                  </div>

	                  <div class="form-group">
	                    <label>Twitter</label>
                      <i style="color: rgba(0,0,0,0.3); font-size:14px; cursor:pointer;" id="TwitterPopup" data-toggle="popover" title="Twitter" data-content="Uygulamanız içerisinde twitter sayfanızı görüntüleyebilirsiniz." class="fa fa-info-circle"></i>
	                    <input id="Twitter" type="text" name="Twitter" placeholder="http://twitter.com/YourPage" maxlength="50" class="form-control">
	                  </div>
	                </div>
              </div>
            </div>
            <script type="text/javascript">
              var startAnime;
              function submitForm() {
                var finalStage=false;
                //validation
                var o, o2;
                o = $("#OrderNo");
                if(o.val().length == 0) {
                    $('#myModal').find('.modal-title').text('Sipariş Numarası');
                    $('#myModal').find('.modal-body p').text('Sipariş numarası alanını boş bıraktınız.');
                    $('#myModal').modal('show');
                    $( "#firstListItem").removeClass('appListSuccess');
                    $( "a#firstListItem").find('i').css('opacity','0.3');
                    $( "a#firstListItem").find('i').css('color','#999');
                  return;
                }

                o = $("#Name");
                if(o.val().length == 0) {
                    $('#myModal').find('.modal-title').text('Uygulama Adı');
                    $('#myModal').find('.modal-body p').text('Uygulama adı alanını boş bıraktınız.');
                    $('#myModal').modal('show');
                    $( "#firstListItem").removeClass('appListSuccess');
                    $( "a#firstListItem").find('i').css('opacity','0.3');
                    $( "a#firstListItem").find('i').css('color','#999');
                  return;
                }

                o = $("#Description");
                if(o.val().length == 0) {
                    $('#myModal').find('.modal-title').text('Uygulama Açıklaması');
                    $('#myModal').find('.modal-body p').text('Uygulama açıklaması alanını boş bıraktınız.');
                    $('#myModal').modal('show');
                    $( "#firstListItem").removeClass('appListSuccess');
                    $( "a#firstListItem").find('i').css('opacity','0.3');
                    $( "a#firstListItem").find('i').css('color','#999');
                  return;
                }

                o = $("#Keywords");
                if(o.val().length == 0) {
                    $('#myModal').find('.modal-title').text('Anahtar Kelimeler');
                    $('#myModal').find('.modal-body p').text('Anahtar kelimeler alanını boş bıraktınız.');
                    $('#myModal').modal('show');
                    $( "#firstListItem").removeClass('appListSuccess');
                    $( "a#firstListItem").find('i').css('opacity','0.3');
                    $( "a#firstListItem").find('i').css('color','#999');
                  return;
                }
             
                if( $('ul li:nth-child(2)').prev().hasClass('active')==true){

                $( "a#firstListItem").removeClass().addClass('appListSuccess');
                $( "a#firstListItem").find('i').css('opacity','1');
                $( "a#firstListItem").find('i').css('color','rgb(0, 202, 16);');
                  $( "#stage1" ).animate( {marginTop: "-1000"}, 500, function() {
                    $( "#stage1" ).addClass('hide');
                      $('#stage2').css('margin-top','-800px').removeClass('hide');
                      $( "#stage2" ).animate({
                        marginTop: "0"
                      }, 1000);
                      $('#appBackButton').removeClass('hide').fadeIn();

                      $( "#detailStage" ).animate( {marginLeft: "100"}, 500, function() {
                        $('#detailStage').text('Uygulama Resimlerini Yükle');
                        $( "#detailStage" ).animate( {marginLeft: "0"},500);
                      });
                  });

                  $('ul li:nth-child(2)').prev().removeClass('active').find('span').removeClass().addClass('liBorders');
                  $('ul li:nth-child(2)').removeClass('disabled').addClass('active').find('span').removeClass().addClass('liBordersActive');

                  startAnime = function(){

                    if( $('ul li:nth-child(2)').hasClass('active')==true){

                      $( ".appimg #launcImages" ).fadeTo( 'slow' , 1);

                      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
                        $( ".appimg img" ).each(function(){
                          $(this).addClass('hide');
                        })
                        $('#imgLaunch1').removeClass('hide');
                        $( ".appimg" ).fadeTo( 'slow' , 1);
                        if( $('ul li:nth-child(2)').hasClass('active')!=true){
                          return;
                        }

                          $( ".appimg" ).delay(3000).fadeTo( 'slow' , 0, function() {
                            $( ".appimg img" ).each(function(){
                              $(this).addClass('hide');
                            })
                            $('#imgLaunch2').removeClass('hide');
                            $( ".appimg" ).fadeTo( 'slow' , 1);

                            if( $('ul li:nth-child(2)').hasClass('active')!=true){
                              return;
                            }

                             $( ".appimg" ).delay(3000).fadeTo( 'slow' , 0, function() {
                              $( ".appimg img" ).each(function(){
                                $(this).addClass('hide');
                              })
                              $('#imgLaunch3').removeClass('hide');
                              $( ".appimg" ).fadeTo( 'slow' , 1);

                              if( $('ul li:nth-child(2)').hasClass('active')!=true){
                                return;
                              }


                                $( ".appimg" ).delay(3000).fadeTo( 'slow' , 0, function() {
                                  $( ".appimg img" ).each(function(){
                                    $(this).addClass('hide');
                                  })
                                  $( ".appimg" ).fadeTo( 'slow' , 1);
                                  if( $('ul li:nth-child(2)').hasClass('active')==true){
                                    setTimeout(function(){
                                      startAnime();
                                    },1500)
                                  }
                                })

                          })
                      })
                    })
                  }

                    else{
                      $( ".appimg #launcImages" ).fadeTo( 'slow' , 0);
                      return;
                    }
                  }

                  startAnime();
                  return;
                }
            
                if($( "ul li:nth-child(2)" ).hasClass("active")==true){

                  if($('#hdnImage1024x1024Selected').val()==0 && $('#hdnPdfSelected').val()==0){
                    $('#myModal').find('.modal-title').text('Logo ve Pdf Dosyası ');
                    $('#myModal').find('.modal-body p').text('1024x1024 çözünürlüklü logo dosyası ile pdf dosyası yüklenmedi!');
                    $('#myModal').modal('show');
                    $( "#secondListItem").removeClass('appListSuccess');
                    $( "a#secondListItem").find('i').css('opacity','0.3');
                    $( "a#secondListItem").find('i').css('color','#999');
                    $(this).parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
                    return;
                  }

                  if($('#hdnImage1024x1024Selected').val()==0){
                    $('#myModal').find('.modal-title').text('Logo');
                    $('#myModal').find('.modal-body p').text('1024x1024 çözünürlüklü logo dosyası yüklenmedi!');
                    $('#myModal').modal('show');
                    $( "#secondListItem").removeClass('appListSuccess');
                    $( "a#secondListItem").find('i').css('opacity','0.3');
                    $( "a#secondListItem").find('i').css('color','#999');
                    $('#hdnImage1024x1024Selected').parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
                    return;
                  }    

                  if($('#hdnPdfSelected').val()==0){
                    $('#myModal').find('.modal-title').text('Pdf Dosyası');
                    $('#myModal').find('.modal-body p').text('Pdf dosyası yüklenmedi!');
                    $('#myModal').modal('show');
                    $( "#secondListItem").removeClass('appListSuccess');
                    $( "a#secondListItem").find('i').css('opacity','0.3');
                    $( "a#secondListItem").find('i').css('color','#999');
                    $('#Pdf').parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
                    return;
                  }

                  $( "a#secondListItem").removeClass().addClass('appListSuccess');
                  $( "a#secondListItem").find('i').css('opacity','1');
                  $( "a#firstListItem").find('i').css('color','rgb(0, 202, 16);');

                  $( "#stage2" ).animate( {marginTop: "-1000"}, 500, function() {
                    $( "#stage2" ).addClass('hide');
                    $('#stage3').css('margin-top','-800px').removeClass('hide');
                    $( "#stage3" ).animate({
                      marginTop: "0"
                    }, 1000);
                  });

                  $( "#detailStage" ).animate( {marginLeft: "100"}, 500, function() {

                    $('#detailStage').text('Uygulama Bilgilerini Gönder');
                    $( "#detailStage" ).animate( {marginLeft: "0"},500);

                    $('ul li:nth-child(2)').removeClass('active').find('span').removeClass().addClass('liBorders');
                    $('ul li:nth-child(3)').removeClass('disabled').addClass('active').find('span').removeClass().addClass('liBordersActive');
                    $('#appSubmitButton').val('Formu Gönder');

                    $( "a#firstListItem").removeClass().addClass('appListSuccess');
                    $( "a#secondListItem").removeClass().addClass('appListSuccess');
                    $( "a#firstListItem").find('i').css('opacity','1');
                    $( "a#secondListItem").find('i').css('opacity','1');
                    $( "a#firstListItem").find('i').css('color','rgb(0, 202, 16);');
                    $( "a#secondListItem").find('i').css('color','rgb(0, 202, 16);');

                    $( ".appimg #launcImages" ).fadeTo( 'slow' , 0);

                  });

                  return;
                  }

                    o = $("#hdnImage1024x1024Selected");
                    o2 = $("#hdnImage1024x1024Name");

                    if(o.val() == 0 && o2.val().length == 0) {
                      $('#myModal').find('.modal-title').text('İkon Dosyası');
                      $('#myModal').find('.modal-body p').text('1024x1024 çözünürlüklü logo dosyası yüklenmedi!');
                      $('#myModal').modal('show');
                      return;
                    }

                    o = $("#hdnPdfSelected");
                    o2 = $("#hdnPdfName");
                    if(o.val() == 0 && o2.val().length == 0) {
                      $('#myModal').find('.modal-title').text('Pdf Dosyası');
                      $('#myModal').find('.modal-body p').text('Pdf dosyası yüklenmedi!');
                      $('#myModal').modal('show');
                      return;
                    }                	

                	$.ajax({
                  type: "POST",
                  url: '/' + $('#currentlanguage').val() + '/' + route["orders_save"],
                  data: $("form").serialize(),
                  success: function(data, textStatus) {
                    if(data.getValue("success") == "true")
                    {

                              $('#hdnImage1024x1024Selected').val(0);
                              $('#hdnImage640x960Selected').val(0);
                              $('#hdnImage640x1136Selected').val(0);
                              $('#hdnImage1536x2048Selected').val(0);
                              $('#hdnImage2048x1536Selected').val(0);
                              $('#hdnPdfSelected').val(0);

                              $('#hdnImage1024x1024Name').val("");
                              $('#hdnImage640x960Name').val("");
                              $('#hdnImage640x1136Name').val("");
                              $('#hdnImage1536x2048Name').val("");
                              $('#hdnImage2048x1536Name').val("");
                              $('#hdnPdfName').val("");

                            $( "#stage3" ).animate( {marginTop: "-1000"}, 500, function() {
                            $( "#stage3" ).addClass('hide');
                              $('#stage4').css('margin-top','-800px').removeClass('hide');
                              $( "#stage4" ).animate({
                                marginTop: "0"
                              }, 1000);
                            });
                            $( ".appimg #launcImages" ).fadeTo( 'slow' , 0);

                            $( "a#thirdListItem").removeClass().addClass('appListSuccess');
                            $( "a#thirdListItem").find('i').css('opacity','1');
                            $( "a#thirdListItem").find('i').css('color','rgb(0, 202, 16);');

                            $('#appSubmitButton').fadeOut();
                            $('#appBackButton').fadeOut();

                            $( "#detailStage" ).animate( {marginLeft: "100"}, 500, function() {
                                $('#detailStage').text('Uygulama Bilgileri Gönderildi!');
                                $( "#detailStage" ).animate( {marginLeft: "0"},500);
                            });

                            $('ul li:nth-child(2)').prev().parent().removeClass('ulDisabled');
                            $('ul li:nth-child(2)').prev().removeClass();
                            $('ul li:nth-child(2)').removeClass();
                            $('ul li:nth-child(3)').removeClass();
                    }
                    else {
                      //error
                    }
                  },
                  error: function (resp) {
                    //error
                      }
                });
                return false;
              }
            </script>
            <div class="form-group hide" id="stage2" style="text-align: center;">
              <fieldset class="scheduler-border">
                  <legend class="scheduler-border"><span style="color: #428bca;font-size: 17px;font-family: monospace; margin-top:-10px; font-weight:bold">*</span> Logo & PDF</legend>
                  <div class="control-group">
                    <ul>
                      <li style="float:left; list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                          <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>1024x1024 çözünürlüklü logo yükle *</div>
                          <input id="Image1024x1024" name="Image1024x1024" type="file" class="upload" required/>
                          <input type="hidden" name="hdnImage1024x1024Selected" id="hdnImage1024x1024Selected" value="0" />
                          <input type="hidden" name="hdnImage1024x1024Name" id="hdnImage1024x1024Name" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Image1024x1024").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PNG Files',
                                'fileTypeExts': '*.png',
                                'buttonText': "1024x1024",
                                'formData': { 
                                  'element': 'Image1024x1024',
                                  'type': 'uploadpng1024x1024'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnImage1024x1024Selected').val("1");
                                  $("[for='Image1024x1024']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Image1024x1024'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image1024x1024'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnImage1024x1024Name').val(fileName);
                                    $("[for='Image1024x1024']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnImage1024x1024Selected').val("0");
                                    $('#hdnImage1024x1024Name').val("");
                                    $("[for='Image1024x1024']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnImage1024x1024Selected').val("0");
                                  $('#hdnImage1024x1024Name').val("");
                                  $("[for='Image1024x1024']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnImage1024x1024Selected').val("0");
                                  $('#hdnImage1024x1024Name').val("");
                                  $("[for='Image1024x1024']").addClass("hide");
                                  //console.log(errorMsg, errorString);
                                }
                              });
                            }
                            else
                            {
                              $("#Image1024x1024").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Image1024x1024',
                                  'type': 'uploadpng1024x1024'
                                },
                                add: function(e, data)
                                {
                                  if(/\.(png)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnImage1024x1024Selected').val("1");
                                    $("[for='Image1024x1024']").removeClass("hide");
                                    
                                    data.context = $("[for='Image1024x1024']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      var template = $("[for='Image1024x1024']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  
                                  $("[for='Image1024x1024'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image1024x1024'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnImage1024x1024Name').val(data.result.fileName);
                                    $("[for='Image1024x1024']").addClass("hide");
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnImage1024x1024Selected').val("0");
                                  $('#hdnImage1024x1024Name').val("");
                                  $("[for='Image1024x1024']").addClass("hide");

                                  //console.log(data);
                                }
                              });
                              
                              //select file
                              $("#Image1024x1024Button").removeClass("hide").click(function(){
                                
                                $("#Image1024x1024").click();
                              });
                            }
                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                      </li>
                      <li style="list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                            <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>PDF<br> dosyası yükle *</div>
                            <input id="Pdf" name="Pdf" type="file" class="upload" required/>
                            <input type="hidden" name="hdnPdfSelected" id="hdnPdfSelected" value="0" />
                            <input type="hidden" name="hdnPdfName" id="hdnPdfName" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Pdf").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PDF Files',
                                'fileTypeExts': '*.pdf',
                                'buttonText': "<?php echo dil_orders_file_select ?>",
                                'formData': { 
                                  'element': 'Pdf',
                                  'type': 'uploadpdf'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnPdfSelected').val("1");
                                  $("[for='Pdf']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Pdf'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Pdf'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnPdfName').val(fileName);
                                    $("[for='Pdf']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnPdfSelected').val("0");
                                    $('#hdnPdfName').val("");
                                    $("[for='Pdf']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnPdfSelected').val("0");
                                  $('#hdnPdfName').val("");
                                  $("[for='Pdf']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnPdfSelected').val("0");
                                  $('#hdnPdfName').val("");
                                  $("[for='Pdf']").addClass("hide");
                                }
                              });
                            }
                            else
                            {
                              $("#Pdf").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Pdf',
                                  'type': 'uploadpdf'
                                },
                                add: function(e, data)
                                {
                                  if(data.files[0].size /1024 /1024>5){

                                    $('.alert').removeClass('alert-info').addClass('alert-error').show();
                                      $('.alert span').text('Pdf dosyasının boyutu en fazla 5 mb olabilir.');
                                      $('html, body').animate({ scrollTop: 0 }, 'slow');
                                      $('#hdnPdfSelected').val("0");
                                    $('#hdnPdfName').val("");
                                      return;

                                  }
                                  if(/\.(pdf)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnPdfSelected').val("1");
                                    $("[for='Pdf']").removeClass("hide");
                                    $("#pdfLoadingStatus").fadeIn();
                                    
                                    data.context = $("[for='Pdf']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      var template = $("[for='Pdf']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  $("#pdfLoadingStatus").text(progress.toFixed(0) + '%');
                                  
                                  $("[for='Pdf'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Pdf'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnPdfName').val(data.result.fileName);
                                    $("[for='Pdf']").addClass("hide");
                                    $("#pdfLoadingStatus").fadeOut();
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnPdfSelected').val("0");
                                  $('#hdnPdfName').val("");
                                  $("[for='Pdf']").addClass("hide");
                                }
                              });
                              
                              //select file
                              $("#PdfButton").removeClass("hide").click(function(){
                                $("#Pdf").click();
                              });
                            }
                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                        <div id="pdfLoadingStatus"  style="border:none; float:right"></div>
                      </li>
                    <ul>
                    <i style="color: rgba(0,0,0,0.3); font-size:24px; cursor:pointer; float:right; margin-bottom:-20px; margin-right:-10px; margin-top:-32px;" id="pdf1024Popup" data-toggle="popover" title="<?php echo "Pdf ve 1024x1024 Logo Dosyası" ?>" data-content="<?php echo dil_APP_STAGE2_all?>" class="fa fa-info-circle"></i>
                  </div>
              </fieldset>
              <fieldset class="scheduler-border">
                  <legend class="scheduler-border">Uygulama Ekran Görüntüleri</legend>
                  <div class="control-group">
                    <ul>
                      <li style="float:left; list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                            <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>640x960 çözünürlüklü resim yükle</div>
                            <input id="Image640x960" name="Image640x960" type="file" class="upload"/>
                            <input type="hidden" name="hdnImage640x960Selected" id="hdnImage640x960Selected" value="0" />
                          <input type="hidden" name="hdnImage640x960Name" id="hdnImage640x960Name" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Image640x960").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PNG Files',
                                'fileTypeExts': '*.png',
                                'buttonText': "<?php echo dil_orders_file_select ?>",
                                'formData': { 
                                  'element': 'Image640x960',
                                  'type': 'uploadpng640x960'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnImage640x960Selected').val("1");
                                  $("[for='Image640x960']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Image640x960'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image640x960'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnImage640x960Name').val(fileName);
                                    $("[for='Image640x960']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnImage640x960Selected').val("0");
                                    $('#hdnImage640x960Name').val("");
                                    $("[for='Image640x960']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnImage640x960Selected').val("0");
                                  $('#hdnImage640x960Name').val("");
                                  $("[for='Image640x960']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnImage640x960Selected').val("0");
                                  $('#hdnImage640x960Name').val("");
                                  $("[for='Image640x960']").addClass("hide");
                                }
                              });
                            }
                            else
                            {
                              $("#Image640x960").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Image640x960',
                                  'type': 'uploadpng640x960'
                                },
                                add: function(e, data)
                                {
                                  if(/\.(png)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnImage640x960Selected').val("1");
                                    $("[for='Image640x960']").removeClass("hide");
                                    
                                    data.context = $("[for='Image640x960']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      var template = $("[for='Image640x960']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  
                                  $("[for='Image640x960'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image640x960'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnImage640x960Name').val(data.result.fileName);
                                    $("[for='Image640x960']").addClass("hide");
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnImage640x960Selected').val("0");
                                  $('#hdnImage640x960Name').val("");
                                  $("[for='Image640x960']").addClass("hide");
                                }
                              });
                              
                              //select file
                              $("#Image640x960Button").removeClass("hide").click(function(){
                                
                                $("#Image640x960").click();
                              });
                            }

                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                      </li>
                      <li style="list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                            <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>640x1136 çözünürlüklü resim yükle</div>
                            <input id="Image640x1136" name="Image640x1136" type="file" class="upload"/>
                            <input type="hidden" name="hdnImage640x1136Selected" id="hdnImage640x1136Selected" value="0" />
                          <input type="hidden" name="hdnImage640x1136Name" id="hdnImage640x1136Name" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Image640x1136").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PNG Files',
                                'fileTypeExts': '*.png',
                                'buttonText': "<?php echo dil_orders_file_select ?>",
                                'formData': { 
                                  'element': 'Image640x1136',
                                  'type': 'uploadpng640x1136'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnImage640x1136Selected').val("1");
                                  $("[for='Image640x1136']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Image640x1136'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image640x1136'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnImage640x1136Name').val(fileName);
                                    $("[for='Image640x1136']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnImage640x1136Selected').val("0");
                                    $('#hdnImage640x1136Name').val("");
                                    $("[for='Image640x1136']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnImage640x1136Selected').val("0");
                                  $('#hdnImage640x1136Name').val("");
                                  $("[for='Image640x1136']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnImage640x1136Selected').val("0");
                                  $('#hdnImage640x1136Name').val("");
                                  $("[for='Image640x1136']").addClass("hide");
                                }
                              });
                            }
                            else
                            {
                              $("#Image640x1136").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Image640x1136',
                                  'type': 'uploadpng640x1136'
                                },
                                add: function(e, data)
                                {
                                  if(/\.(png)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnImage640x1136Selected').val("1");
                                    $("[for='Image640x1136']").removeClass("hide");
                                    
                                    data.context = $("[for='Image640x1136']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      var template = $("[for='Image640x1136']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  
                                  $("[for='Image640x1136'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image640x1136'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnImage640x1136Name').val(data.result.fileName);
                                    $("[for='Image640x1136']").addClass("hide");
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnImage640x1136Selected').val("0");
                                  $('#hdnImage640x1136Name').val("");
                                  $("[for='Image640x1136']").addClass("hide");
                                }
                              });
                              
                              //select file
                              $("#Image640x1136Button").removeClass("hide").click(function(){
                                
                                $("#Image640x1136").click();
                              });
                            }
                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                      </li>
                      <li style="float:left; list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                            <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>1536x2048 çözünürlüklü resim yükle</div>
                            <input id="Image1536x2048" name="Image1536x2048" type="file" class="upload"/>
                            <input type="hidden" name="hdnImage1536x2048Selected" id="hdnImage1536x2048Selected" value="0" />
                          <input type="hidden" name="hdnImage1536x2048Name" id="hdnImage1536x2048Name" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Image1536x2048").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PNG Files',
                                'fileTypeExts': '*.png',
                                'buttonText': "<?php echo dil_orders_file_select ?>",
                                'formData': { 
                                  'element': 'Image1536x2048',
                                  'type': 'uploadpng1536x2048'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnImage1536x2048Selected').val("1");
                                  $("[for='Image1536x2048']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Image1536x2048'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image1536x2048'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnImage1536x2048Name').val(fileName);
                                    $("[for='Image1536x2048']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnImage1536x2048Selected').val("0");
                                    $('#hdnImage1536x2048Name').val("");
                                    $("[for='Image1536x2048']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnImage1536x2048Selected').val("0");
                                  $('#hdnImage1536x2048Name').val("");
                                  $("[for='Image1536x2048']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnImage1536x2048Selected').val("0");
                                  $('#hdnImage1536x2048Name').val("");
                                  $("[for='Image1536x2048']").addClass("hide");
                                }
                              });
                            }
                            else
                            {
                              $("#Image1536x2048").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Image1536x2048',
                                  'type': 'uploadpng1536x2048'
                                },
                                add: function(e, data)
                                {
                                  if(/\.(png)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnImage1536x2048Selected').val("1");
                                    $("[for='Image1536x2048']").removeClass("hide");
                                    
                                    data.context = $("[for='Image1536x2048']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      var template = $("[for='Image1536x2048']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  
                                  $("[for='Image1536x2048'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image1536x2048'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnImage1536x2048Name').val(data.result.fileName);
                                    $("[for='Image1536x2048']").addClass("hide");
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnImage1536x2048Selected').val("0");
                                  $('#hdnImage1536x2048Name').val("");
                                  $("[for='Image1536x2048']").addClass("hide");
                                }
                              });
                              
                              //select file
                              $("#Image1536x2048Button").removeClass("hide").click(function(){
                                
                                $("#Image1536x2048").click();
                              });
                            }
                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                      </li>
                      <li style="list-style:none; background:none;">
                        <div class="fileUpload btn btn-primary">
                            <div class="noWhiteSpace"><i class="fa fa-cloud-upload" style="font-size:18px;"></i><br>2048x1536 çözünürlüklü resim yükle</div>
                            <input id="Image2048x1536" name="Image2048x1536" type="file" class="upload"/>
                            <input type="hidden" name="hdnImage2048x1536Selected" id="hdnImage2048x1536Selected" value="0" />
                          <input type="hidden" name="hdnImage2048x1536Name" id="hdnImage2048x1536Name" value="" />
                          <script type="text/javascript">
                          $(function(){

                            if($("html").hasClass("lt-ie10") || $("html").hasClass("lt-ie9") || $("html").hasClass("lt-ie8"))
                            {
                              $("#Image2048x1536").uploadify({
                                'swf': '/uploadify/uploadify.swf',
                                'uploader': '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile2"],
                                'cancelImg': '/uploadify/uploadify-cancel.png',
                                'fileTypeDesc': 'PNG Files',
                                'fileTypeExts': '*.png',
                                'buttonText': "<?php echo dil_orders_file_select ?>",
                                'formData': { 
                                  'element': 'Image2048x1536',
                                  'type': 'uploadpng2048x1536'
                                },
                                'multi': false,
                                'auto': true,
                                'successTimeout': 300,
                                'onSelect': function (file) {
                                  $('#hdnImage2048x1536Selected').val("1");
                                  $("[for='Image2048x1536']").removeClass("hide");
                                },
                                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                                  var progress = totalBytesUploaded / totalBytesTotal * 100;
                                  $("[for='Image2048x1536'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image2048x1536'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                'onUploadSuccess': function (file, data, response) {

                                  if(data.getValue("success") == "true")
                                  {
                                    var fileName = data.getValue("filename");

                                    $('#hdnImage2048x1536Name').val(fileName);
                                    $("[for='Image2048x1536']").addClass("hide");
                                  }
                                  else {
                                    $('#hdnImage2048x1536Selected').val("0");
                                    $('#hdnImage2048x1536Name').val("");
                                    $("[for='Image2048x1536']").addClass("hide");
                                  }
                                },
                                'onCancel': function(file) {
                                  $('#hdnImage2048x1536Selected').val("0");
                                  $('#hdnImage2048x1536Name').val("");
                                  $("[for='Image2048x1536']").addClass("hide");
                                },
                                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                                  $('#hdnImage2048x1536Selected').val("0");
                                  $('#hdnImage2048x1536Name').val("");
                                  $("[for='Image2048x1536']").addClass("hide");
                                }
                              });
                            }
                            else
                            {
                              $("#Image2048x1536").fileupload({
                                url: '/' + $('#currentlanguage').val() + '/' + route["orders_uploadfile"],
                                dataType: 'json',
                                sequentialUploads: true,
                                formData: { 
                                  'element': 'Image2048x1536',
                                  'type': 'uploadpng2048x1536'
                                },
                                add: function(e, data)
                                {
                                  if(/\.(png)$/i.test(data.files[0].name))
                                  {
                                    $('#hdnImage2048x1536Selected').val("1");
                                    $("[for='Image2048x1536']").removeClass("hide");
                                    
                                    data.context = $("[for='Image2048x1536']");
                                    data.context.find('a').click(function(e){
                                      e.preventDefault();
                                      $('#hdnImage2048x1536Selected').val("0");
                                      $('#hdnImage2048x1536Name').val("");
                                      $("[for='Image2048x1536']").addClass("hide");
                                      var template = $("[for='Image2048x1536']");
                                      data = template.data('data') || {};
                                      if(data.jqXHR)
                                      {
                                        data.jqXHR.abort();
                                      }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                                  }
                                },
                                progressall: function(e, data)
                                {
                                  var progress = data.loaded / data.total * 100;
                                  
                                  $("[for='Image2048x1536'] label").html(progress.toFixed(0) + '%');
                                  $("[for='Image2048x1536'] div.scale").css('width', progress.toFixed(0) + '%');
                                },
                                done: function(e, data)
                                {
                                  if(data.textStatus == 'success')
                                  {
                                    $('#hdnImage2048x1536Name').val(data.result.fileName);
                                    $("[for='Image2048x1536']").addClass("hide");
                                  }
                                },
                                fail: function(e, data)
                                {
                                  $('#hdnImage2048x1536Selected').val("0");
                                  $('#hdnImage2048x1536Name').val("");
                                  $("[for='Image2048x1536']").addClass("hide");
                                }
                              });
                              
                              //select file
                              $("#Image2048x1536Button").removeClass("hide").click(function(){
                                
                                $("#Image2048x1536").click();
                              });
                            }
                          });
                          </script>
                        </div>
                        <i class="fa fa-check-circle hide"></i>
                      </li>
                    </ul>
                    <i style="color: rgba(0,0,0,0.3); font-size:24px; cursor:pointer; float:right; margin-bottom:-12px; margin-right:-10px; margin-top:-42px;" id="otherImages" data-toggle="popover" title="Uygulama Görüntüleri" data-content="Uygulamanıza ait farklı çözünürlükteki görüntüler cihazlar bazında görüntülenecektir." class="fa fa-info-circle"></i>
                  </div>
              </fieldset>
            </div>
          	<div class="form-group hide" id="stage3">
    						<div class="row controls" style="padding-left:45px;">
    							<h3>Başarılı! Uygulama bilgileriniz hazır durumda.<br><br> Bilgilerinizi kontrol ettikten sonra formu gönderebilirsiniz.</h3>
    						</div>
            </div>
            <div class="form-group hide" id="stage4">
                <div class="row controls" style="padding-left:45px;">
                  <br>
                  <h3>Uygulama bilgileriniz başarıyla tarafımıza iletilmiştir!</h3>
                </div>
            </div>
            <hr class="tall" style="margin: 1px 0 9px 0; height:1px; clear:both;">
            <input type="button" value="İleri" class="btn btn-primary pull-right" id="appSubmitButton" onclick="submitForm()" style="width: 23%;">
            <input type="button" class="btn btn-info hide" id="appBackButton" value="Geri">
    			</form>
        </div>
        <div class="col-lg-6 appimg">
          <!--<img src="/website/app-form/images/test.jpg" class="hide" id="imgOrderNo">-->
          <img src="/website/app-form/images/yeni2/04.png" class="hide" id="imgAppName">
          <img src="/website/app-form/images/yeni2/02.png" class="hide" id="imgAppDesc">
          <img src="/website/app-form/images/yeni2/01.png" class="hide" id="imgKeywords">
          <img src="/website/app-form/images/yeni2/03.png" class="hide" id="imgEmail">
          <img src="/website/app-form/images/yeni2/03.png" class="hide" id="imgWebSite">
          <img src="/website/app-form/images/yeni2/03.png" class="hide" id="imgFacebook">
          <img src="/website/app-form/images/yeni2/03.png" class="hide" id="imgTwitter">
          <div id="launcImages">
            <img src="/website/app-form/images/yeni2/05.png" class="hide" id="imgLaunch1">
            <img src="/website/app-form/images/yeni2/06.png" class="hide" id="imgLaunch2">
            <img src="/website/app-form/images/yeni2/07.png" class="hide" id="imgLaunch3">
          </div>
        </div>
      </div><!-- /.row -->
    </div><!-- /#page-wrapper -->
  </div><!-- /#wrapper -->
	<div class="modal fade" id="myModal">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header" style="background: rgb(255, 239, 239);">
	        <button type="button" class="close" data-dismiss="modal" id="modal-closeBtn"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        <i class="fa fa-exclamation-triangle" style="float: left;font-size: 25px; color: rgb(199, 40, 40);">&nbsp;</i><h4 class="modal-title">Başlık</h4>
	      </div>
	      <div class="modal-body">
	        <p style="margin:0; font-size:17px;">İçerik</p>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-primary" data-dismiss="modal" style="outline:0;">Tamam</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<script type="text/javascript">
	$(document).ready(function() {

    $("#OrderNoPopup").popover();
    $("#AppNamePopup").popover();
    $("#AppDescPopup").popover();
    $("#KeywordsPopup").popover();

    $("#EmailPopup").popover();
    $("#WebSitePopup").popover();
    $("#FacePopup").popover();
    $("#TwitterPopup").popover();

    $("#pdf1024Popup").popover();
    $("#otherImages").popover();

		$('textarea').inputlimiter({ remText: '%n', limitText: '(%n) karakter', });
		$('#Name').inputlimiter({ remText: '%n', limitText: '(%n) karakter', });
    $('#OrderNo').inputlimiter({ remText: '%n', limitText: '(%n) karakter', });

		var _URL = window.URL || window.webkitURL;

		function imageDimensionCheck(input, w, h, type, size){

			if(type=='png'){

				if(size>5){
  					$('#myModal').find('.modal-title').text('Resim Dosya Boyutu');
            $('#myModal').find('.modal-body p').text('Resim dosyasının boyutu en fazla 5 mb olabilir.');
            $('#myModal').modal('show');
			    	$( "ul li:nth-child(2) a" ).removeClass('appListSuccess');
			    	$('html, body').animate({ scrollTop: 0 }, 'slow');
            $('#'+input.id).parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
            $( "#secondListItem").removeClass('appListSuccess');
            $( "a#secondListItem").find('i').css('opacity','0.3');
            $( "a#secondListItem").find('i').css('color','#999');
			    	return;
				}
				else{
					var file, img;
					if ((file = input.files[0])){
              
					    img = new Image();
					    img.onload = function () {
						  var imgWidth = this.width;
						  var imgHeight = this.height;
						    	if(imgWidth!=w || imgHeight!=h){
							    	$('#myModal').find('.modal-title').text('Resim Çözünürlüğü');
                    $('#myModal').find('.modal-body p').text('Resminiz '+w+'x'+h+' çözünürlüğünde olmalıdır.');
                    $('#myModal').modal('show');
							    	$( "ul li:nth-child(2) a" ).removeClass('appListSuccess');
							    	$('html, body').animate({ scrollTop: 0 }, 'slow');
                    
                    $('#'+input.id).parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
                    $( "#secondListItem").removeClass('appListSuccess');
                    $( "a#secondListItem").find('i').css('opacity','0.3');
                    $( "a#secondListItem").find('i').css('color','#999');
							    	return;
							    }
							    else{
							    		$( "ul li:nth-child(2) a" ).addClass('appListSuccess');
                      $('#'+input.id).parent().next().removeClass().addClass('fa fa-check-circle').removeClass('hide').css('color','rgb(0, 202, 16);').fadeIn();
							    }    
					    };
					    img.src = _URL.createObjectURL(file);
					}
				}
			}
		  else{
		    	$('#myModal').find('.modal-title').text('Resim Dosyası');
          $('#myModal').find('.modal-body p').text('Resim dosyanızın uzantısı png olmalıdır.');
          $('#myModal').modal('show');
		    	$( "ul li:nth-child(2) a" ).removeClass('appListSuccess');
		    	$('html, body').animate({ scrollTop: 0 }, 'slow');
          $('#'+input.id).parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
          $( "#secondListItem").removeClass('appListSuccess');
          $( "a#secondListItem").find('i').css('opacity','0.3');
          $( "a#secondListItem").find('i').css('color','#999');
		    	return;
			}
		}

		var fileType, fileSize;
		$("#Image1024x1024").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			imageDimensionCheck(this, 1024, 1024, fileType[1], fileSize);
		});

		$("#Image640x960").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			imageDimensionCheck(this, 640, 960, fileType[1], fileSize);
		});

		$("#Image640x1136").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			imageDimensionCheck(this, 640, 1136, fileType[1], fileSize);
		});

		$("#Image1536x2048").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			imageDimensionCheck(this, 1536, 2048, fileType[1], fileSize);
		});

		$("#Image2048x1536").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			imageDimensionCheck(this, 2048, 1536, fileType[1], fileSize);
		});

		$('#appBackButton').on('click',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0);
      $( ".appimg #launcImages" ).fadeTo( 'slow' , 0);
      if( $('ul li:nth-child(3)').hasClass('active')==true){

      setTimeout(function(){
        startAnime();
      },2000)
        
      }
			$( "#appSubmitButton" ).val("İleri");
			if($( "ul li:nth-child(2)" ).hasClass("active")==true){
				 $( "#stage2" ).animate( {marginTop: "-1000"}, 500, function() {
                	$( "#stage2" ).addClass('hide');
                	$( "#stage3" ).addClass('hide');
                	$( "#stage4" ).addClass('hide');
                  $('#stage1').css('margin-top','-800px').removeClass('hide');
                  $( "#stage1" ).animate({
					    marginTop: "0"
					  }, 1000);
                    $( "#appBackButton" ).fadeOut();
                    $( "ul li:nth-child(2)" ).removeClass("active");
                    $( "ul li:nth-child(2)" ).prev().addClass("active");
	          });

        $('ul li:nth-child(2)').prev().addClass('active').find('span').removeClass().addClass('liBordersActive');
        $('ul li:nth-child(2)').removeClass('disabled').removeClass('active').find('span').removeClass().addClass('liBorders');

				 $( "#detailStage" ).animate( {marginLeft: "100"}, 500, function() {
                        	$('#detailStage').text('Uygulama Detaylarını Gir');
                        	$( "#detailStage" ).animate( {marginLeft: "0"},500);
                        });
			}
			if($( "ul li:nth-child(3)" ).hasClass("active")==true){
				 $( "#stage3" ).animate( {marginTop: "-1000"}, 500, function() {
                	$( "#stage3" ).addClass('hide');
                	$( "#stage1" ).addClass('hide');
                	$( "#stage4" ).addClass('hide');
                    $('#stage2').css('margin-top','-800px').removeClass('hide');
                    $( "#stage2" ).animate({
					    marginTop: "0"
					  }, 1000);
                    $( "ul li:nth-child(2)" ).removeClass().addClass("active");
                    $( "ul li:nth-child(3)" ).removeClass("active");
	            });

        $('ul li:nth-child(2)').addClass('active').find('span').removeClass().addClass('liBordersActive');
        $('ul li:nth-child(3)').removeClass('disabled').removeClass('active').find('span').removeClass().addClass('liBorders');

				$( "#detailStage" ).animate( {marginLeft: "100"}, 500, function() {
        	$('#detailStage').text('Uygulama Resimlerini Yükle');
        	$( "#detailStage" ).animate( {marginLeft: "0"},500);
        });
			}
		});

		$('.close').on('click',function(){
			if(this.id!='modal-closeBtn')
		  		$(this).parent().hide();
		});
		$('.modal-okBtn').on('click',function(){
		  $('#myModal').modal('hide');
		});

		$('#myModal').on('hidden.bs.modal', function (e) {
			setTimeout(function() {
			      $('.alert').fadeOut(3500);
			      $('.file-status').fadeOut(3500);
			});
		})
		$("#Pdf").change(function (e) {
			fileType=this.files[0].type.split('/');
			fileSize= (this.files[0].size) /1024 /1024;
			if(fileType[1]!='pdf'){
		    	$('#myModal').find('.modal-title').text('Pdf Dosyası');
          $('#myModal').find('.modal-body p').text('Pdf dosyası yüklenmedi!');
          $('#myModal').modal('show');
		    	$( "ul li:nth-child(2) a" ).removeClass('appListSuccess');
		    	$('html, body').animate({ scrollTop: 0 }, 'slow');
		    	$('#hdnPdfSelected').val("0");
				  $('#hdnPdfName').val("");
          $("#Pdf").parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
          $( "#secondListItem").removeClass('appListSuccess');
          $( "a#secondListItem").find('i').css('opacity','0.3');
          $( "a#secondListItem").find('i').css('color','#999');
		    	return;
		    }
		    else{
		    	if(fileSize>5){
		    		$('#myModal').find('.modal-title').text('Pdf Dosya Boyutu');
            $('#myModal').find('.modal-body p').text('Pdf dosyanızın boyutu en fazla 5 mb olmalıdır!');
            $('#myModal').modal('show');
			    	$( "ul li:nth-child(2) a" ).removeClass('appListSuccess');
			    	$('html, body').animate({ scrollTop: 0 }, 'slow');
			    	$('#hdnPdfSelected').val("0");
					  $('#hdnPdfName').val("");
            $("#Pdf").parent().next().removeClass().addClass('fa fa-exclamation-triangle').removeClass('hide').css('color','red').fadeIn();
            $( "a#secondListItem").find('i').css('opacity','0.3');
            $( "a#secondListItem").find('i').css('color','#999');
			    	return;
		    	}
		    	else{
						$( "ul li:nth-child(2) a" ).addClass('appListSuccess');	
            $("#Pdf").parent().next().removeClass().addClass('fa fa-check-circle').removeClass('hide').css('color','rgb(0, 202, 16);').fadeIn();
		    	}
		    }
		})

    $('#OrderNo').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
      });
    });

    $('#Name').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgAppName').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Description').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgAppDesc').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Keywords').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgKeywords').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Email').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgEmail').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Website').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgWebSite').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Facebook').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgFacebook').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });
    $('#Twitter').on('focus',function(){
      $( ".appimg" ).fadeTo( 'slow' , 0, function() {
        $( ".appimg img" ).each(function(){
          $(this).addClass('hide');
        })
        $('#imgTwitter').removeClass('hide');
        $( ".appimg" ).fadeTo( 'slow' , 1);
      });
    });

    $( "#firstListItem").click(function(){
      $( "#stage2" ).addClass('hide');
      $( "#stage3" ).addClass('hide');
      $( "#stage4" ).addClass('hide'); 
      $( "#stage1" ).removeClass().fadeIn().css('margin-top','0');       
    })
    $( "#secondListItem").click(function(){
      $( "#stage1" ).addClass('hide');
      $( "#stage3" ).addClass('hide');
      $( "#stage4" ).addClass('hide'); 
      $( "#stage2" ).removeClass().fadeIn().css('margin-top','0');        
    })
    $( "#thirdListItem").click(function(){
      $( "#stage1" ).addClass('hide');
      $( "#stage2" ).addClass('hide');
      $( "#stage3" ).addClass('hide');  
      $( "#stage4" ).removeClass().fadeIn().css('margin-top','0');       
    })
	});
	</script>
	</div>
	<!-- JavaScript -->
  <script src="/website/app-form/js/bootstrap.js"></script>
	<script src="/website/js/jquery.inputlimiter.1.3.1.min.js"></script>
</body>
</html>