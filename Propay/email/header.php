<?php
function email_head($input_data) {
    if(function_exists( 'is_rtl' ) ) {
        if( is_rtl() ) {
            $rtl = true;
        } else {
            $rtl = false;
        }
    } else {
        $rtl = true;
    }
ob_start();
?>
<!DOCTYPE html>
<html dir= <?php echo $rtl ? 'rtl' : 'ltr'?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	</head>
	<body <?php echo $rtl ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div id="wrapper" dir="<?php echo $rtl ? 'rtl' : 'ltr'?>" style="background-color:#f5f5f5;margin:0;padding:70px 0 70px 0;width:100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr style="height: 70px;">
					<td align="left" valign="top">
						<div style="width: 600px; margin: auto; display: block;">
							<!-- <img class=" preload-me" src="http://trumpetalert.com/wp-content/uploads/2017/03/trumpet-email.png" srcset="http://trumpetalert.com/wp-content/uploads/2017/01/trumpet-logo-finalie.png 250w, http://trumpetalert.com/wp-content/uploads/2017/01/trumpet-logo-finalie.png 250w" width="250" height="70" sizes="250px" alt=""> -->
						</div>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
							<tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style='background-color:#6a9c78;border-radius:3px 3px 0 0!important;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif'>
										<tr>
											<td id="header_wrapper" style="padding:36px 48px;display:block">
												<h1 style='color:#ffffff;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left' >
                                                 <?php echo $input_data['header']; ?>
                                                </h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
										<tr>
											<td valign="top" id="body_content">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tr>
														<td valign="top" style="background-color:#fdfdfd;">
															<div id="body_content_inner" style='color:#737373;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left'>
<?php
$message = ob_get_clean();

return $message;

}
?>
