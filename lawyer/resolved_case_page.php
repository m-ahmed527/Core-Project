<?php

	include 'db.php';
	session_start();

	// for user notification
	$select_new_case_request_stmt = $conn->prepare(
		"SELECT COUNT(*) FROM cases WHERE 
		client_email = :client_email AND notification_status=:notification_status"
	);
	$select_new_case_request_stmt->execute(
		[
			'client_email'				=>	$_SESSION['email'],
			'notification_status'	=>	'user_unread/lawyer_read'
		]
	);
	$select_new_case_request_rslt = $select_new_case_request_stmt->fetchAll();
	foreach($select_new_case_request_rslt as $unread_case){}

	$select_resolved_case_stmt = $conn->prepare(
		"SELECT * FROM cases WHERE
		case_status=:case_status AND
		lawyer_email=:lawyer_email"
	);
	$select_resolved_case_stmt->execute(
		[
			'case_status' 	=>	'case resolved', 
			'lawyer_email'	=>	$_SESSION['email']
		]
	);
	$select_resolved_case_rslt = $select_resolved_case_stmt->fetchAll();

	if(isset($_SESSION['email'], $_SESSION['phone'])){
		$select_stmt = $conn->prepare(
			"SELECT * FROM lawyer WHERE 
			email=:email AND 
			phone=:phone"
		);
		$select_stmt->execute(
			[
				'email'=>$_SESSION['email'], 
				'phone'=>$_SESSION['phone']
			]
		);
		$select_rslt = $select_stmt->fetchAll();
		foreach($select_rslt as $row){}

?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Advogate- Lawyer Resolved Case Page</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Advogate aims to bridge the gap between advocates and clients. It allows user to find the best available lawyer as per thier required case in super affordable price" name="description">
    <meta content="" name="keywords">
    <meta content="" name="author">
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <![endif]-->
    <!-- CSS Files
    ================================================== -->
    <link id="bootstrap" href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link id="bootstrap-grid" href="css/bootstrap-grid.min.css" rel="stylesheet" type="text/css" />
    <link id="bootstrap-reboot" href="css/bootstrap-reboot.min.css" rel="stylesheet" type="text/css" />
    <link href="css/animate.css" rel="stylesheet" type="text/css">
    <link href="css/owl.carousel.css" rel="stylesheet" type="text/css">
    <link href="css/owl.theme.css" rel="stylesheet" type="text/css">
    <link href="css/owl.transitions.css" rel="stylesheet" type="text/css">
    <link href="css/magnific-popup.css" rel="stylesheet" type="text/css">
    <link href="css/jquery.countdown.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <!-- color scheme -->
    <link id="colors" href="css/colors/scheme-01.css" rel="stylesheet" type="text/css">
		<link href="css/coloring.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="vendor/materializeicon/material-icons.css">
		<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&amp;display=swap" rel="stylesheet">
		<link href="vendor/bootstrap-4.4.1/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link href="vendor/swiper/css/swiper.min.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <div id="topbar" class="text-white bg-color">
            <div class="container">
                <div class="topbar-left sm-hide">
                    <span class="topbar-widget tb-social">
                        <a href="#"><i class="fa fa-facebook"></i></a>
                        <a href="#"><i class="fa fa-twitter"></i></a>
                        <a href="#"><i class="fa fa-instagram"></i></a>
                    </span>
                </div>
                <div class="topbar-right">
                    <div class="topbar-right">
                        <span class="topbar-widget"><a href="#">Privacy policy</a></span>
                        <span class="topbar-widget"><a href="#">Request Quote</a></span>
                        <span class="topbar-widget"><a href="#">FAQ</a></span>
                        <span class="topbar-widget"><a href="log-out.php">Logout</a></span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- header begin -->
        <header class="transparent">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="de-flex sm-pt10">
                            <div class="de-flex-col">
                                <!-- logo begin -->
                                <div id="logo">
                                    <a href="indexdata.php">
                                        <img alt="" class="logo" src="../images/logo1-light1.png" />
                                        <img alt="" class="logo-2" src="../images/logo1.png" />
                                    </a>
                                </div>
                                <!-- logo close -->
                            </div>
                            <div class="de-flex-col header-col-mid">
                                <!-- mainmenu begin -->
                                <ul id="mainmenu">
                                    <li><a href="indexdata.php">Home</a></li>
                                    <li><a href="notification.php">Notifications</a></li>
                                    <li><a href="transactions.php">Transactions</a></li>
                                    <li><a href="profile.php">Profile</a></li>
                                    <!-- <li><a href="about.php">About</a>
                                        <ul>
                                            <li><a href="about.php">About Us</a></li>
                                            <li><a href="team.php">The Team</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="practice-areas.php">Practice Areas</a></li>
                                    <li><a href="news.php">News</a></li>
                                    <li><a href="contact.php">Contact</a></li> -->
                                </ul>
                                <!-- mainmenu close -->
                            </div>
                            <div class="de-flex-col">
                                <div class="h-phone md-hide"><span>Need&nbsp;Help?</span><i class="fa fa-phone"></i>92 21 35220318</div>
                                <span id="menu-btn"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- header close -->
        <!-- content begin -->
        <div class="no-bottom no-top" id="content">
            <div id="top"></div>
            <!-- section begin -->
            <section id="subheader" class="text-light" data-stellar-background-ratio=".2" data-bgimage="url(../images/background/subheader1.jpg) top">
                <div class="center-y relative text-center">
                    <div class="container">
                        <div class="row">
                            <div class="col text-center">
                                <div class="spacer-single"></div>
                                <h1>Resolved Cases</h1>
                                <!-- <p>Reputation. Respect. Result.</p> -->
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- section close -->
            <section>
            <div class="container">
            <div class="card bg-template shadow mt-4 h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <figure class="avatar avatar-60"><img src="../images/user1.png" alt=""></figure>
                        </div>
                        <div class="col pl-0 align-self-center">
                            <h5 class="mb-1"><!-- Ammy Jahnson --><?php echo $row['fullname']; ?></h5>
                            <!-- <p class="text-mute small">Work, London, UK</p> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="container top-100">
            <div class="card mb-4 shadow">
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col">
                            <h3 class="mb-0 font-weight-normal">$ 1548.00</h3>
                            <p class="text-mute">My Balance</p>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-default btn-rounded-54 shadow" data-toggle="modal" data-target="#addmoney"><i class="material-icons">add</i></button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-none">
                    <div class="row">
                        <div class="col">
                            <p>71.00 <i class="material-icons text-danger vm small">arrow_downward</i><br><small class="text-mute">INR</small></p>
                        </div>
                        <div class="col text-center">
                            <p>1.00 <i class="material-icons text-success vm small">arrow_upward</i><br><small class="text-mute">USD</small></p>
                        </div>
                        <div class="col text-right">
                            <p><i class="material-icons text-success vm small mr-1">arrow_upward</i>0.78<br><small class="text-mute">GBP</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="container">
        <h6 class="subtitle">Resolved Cases</h6>
        <div class="card shadow border-0 mb-3">
                <div class="card-body">
                    <div class="row" >
                        <?php
                            foreach($select_resolved_case_rslt as $row_resolved){
                                echo '<div class="col" onclick="location.href=\'resolved_case_detail.php?ucid='.$row_resolved['unique_case_id'].'\'">
                                                <h5 class="font-weight-normal mb-1">'.$row_resolved['case_category'].'</h5>
                                                <p class="text-mute small text-secondary mb-2">'.$row_resolved['case_status'].'</p>
                                                <div class="progress h-4">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width:100%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>';
                            }
                        ?>
                        <!-- <div class="col-auto mr-3 align-self">
                            <div class="row">
                                <div class="avatar avatar-40 no-shadow border-0">
                                    <img src="../images/user2.png" alt="">
                                </div>
                            </div>
                            <div class="row">
                                <p class="small">Lawyer Name</p>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>

        </div>
            </section>
        </div>
        <a href="#" id="back-to-top"></a>
        <!-- footer begin -->
        <footer>
            <<div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="widget">
                            <a href="index.php"><img alt="" class="img-fluid mb20" src="../images/logo1-light1.png"></a>
                            <address class="s1">
                                <span><i class="id-color fa fa-map-marker fa-lg"></i>Kashif Center, Near Hotel Mehran, Main Shahrah-e-Faisal, Karachi</span>
                                <span><i class="id-color fa fa-phone fa-lg"></i>+92 21 35220318</span>
                                <span><i class="id-color fa fa-envelope-o fa-lg"></i><a href="mailto:contact@example.com">support@advogate.com.pk</a></span>
                            </address>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h5 class="id-color mb20">Practice Areas</h5>
                        <ul class="ul-style-2">
                            <li>Corporate and M&A</li>
                            <li>Construction and Real Estate</li>
                            <li>Commercial Duspute Resolution</li>
                            <li>Employment</li>
                            <li>Banking and Finance</li>
                        </ul>
                    </div>
                    <div class="col-lg-4">
                        <div class="widget">
                            <h5 class="id-color">Newsletter</h5>
                            <p>Signup for our newsletter to get the latest news, updates and special offers in your inbox.</p>
                            <form action="blank.php" class="row" id="form_subscribe" method="post" name="form_subscribe">
                                <div class="col text-center">
                                    <input class="form-control" id="name_1" name="name_1" placeholder="enter your email" type="text" /> <a href="#" id="btn-submit"><i class="fa fa-long-arrow-right"></i></a>
                                    <div class="clearfix"></div>
                                </div>
                            </form>
                            <div class="spacer-10"></div>
                            <small>Your email is safe with us. We don't spam.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="subfooter">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="de-flex">
                                <div class="de-flex-col">
                                    &copy; Copyright 2020 - Justica by Designesia
                                </div>
                                <div class="de-flex-col">
                                    <div class="social-icons">
                                        <a href="#"><i class="fa fa-facebook fa-lg"></i></a>
                                        <a href="#"><i class="fa fa-twitter fa-lg"></i></a>
                                        <a href="#"><i class="fa fa-linkedin fa-lg"></i></a>
                                        <a href="#"><i class="fa fa-pinterest fa-lg"></i></a>
                                        <a href="#"><i class="fa fa-rss fa-lg"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
				<!-- footer close -->
				<div class="modal fade" id="casemodal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header border-0">
								<h5> Case Details </h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<form action="indexdata.php" method="post">
								<div class="modal-body pt-0">
									<input type="hidden" name="case_date" value="<?php echo date('d/m/y'); ?>">
									<div class="form-group">
										<!-- <label for="select location">Location</label> -->
										<select class="form-control form-control-lg text-center" id="select location" name="case_location">
											<option value="">Select Location</option>
											<option value="karachi">Karachi</option>
											<option value="lahore">Lahore</option>
											<option value="islamabad">Islamabad</option>
										</select>
									</div>
									<div class="form-group">
										<!-- <label>Case Category</label> -->
										<select name="case_category" class="form-control form-control-lg text-center" onchange="myFunction()" id="case_category">
											<option value="">Select Case Category</option selected>
											<option value="Civil Law">Civil Law</option>
											<option value="Criminal Law">Criminal Law</option>
											<option value="Corporate Law">Corporate Law</option>
											<option value="Consumer Law">Consumer Law</option>
											<option value="General Law">General Law</option>
											<option value="Real Estate Law">Real Estate Law</option>
											<option value="Banking Law">Banking Law</option>
											<option value="Immigration Law">Immigration Law</option>
										</select>
									</div>
									<div id="sub">

									</div>
									<div class="form-group">
										<!-- <label>Case Sub-Category</label> -->
										<select name="case_subcategory" class="form-control form-control-lg text-center" id="case_subcategory">
											<option value="">Select Case Sub-Category</option selected>
										</select>
									</div>
									<div class="form-group">
										<!-- <label>Case Query</label> -->
										<select name="case_query" class="form-control form-control-lg text-center" id="case_query">
											<option value="">Select Case Query</option selected>
										</select>
									</div>
									<div class="form-group mt-2">
										<!-- <label for="">Case Details</label> -->
										<textarea name="case_detail" class="form-control form-control-lg" rows="5" placeholder="More Details"></textarea>
									</div>
									<!-- <div class="form-group mt-2">
										<label for="file">Documents</label>
										<input type="file" name="case_file" class="form-control" id="file">
									</div> -->
								</div>
								<div class="modal-footer border-0">
									<!-- <button type="button" name="" class="btn btn-default btn-lg btn-rounded shadow btn-block" class="close" data-dismiss="modal">Done</button> -->
									<input type="submit" name="case_submit" class="btn btn-default btn-lg btn-rounded shadow btn-block" value="Proceed">
								</div>
							</form>
						</div>
					</div>
				</div>
        <div id="preloader">
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>
    </div>
    <!-- Javascript Files
    ================================================== -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/easing.js"></script>
    <script src="js/owl.carousel.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/enquire.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/jquery.plugin.js"></script>
    <script src="js/typed.js"></script>
    <script src="js/jquery.countTo.js"></script>
    <script src="js/jquery.countdown.js"></script>
    <script src="js/designesia.js"></script>
</body>

</html>
<?php
	} else{
			header('location: log-out.php');
	}
?>