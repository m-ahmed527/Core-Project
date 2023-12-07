<?php
	include 'db.php';
	session_start();
	
	if(isset($_SESSION['email'], $_SESSION['phone'])){		

		if(!empty($_GET['unique_case_id']) && !empty($_GET['client_email']) && !empty($_GET['case_category']) && !empty($_GET['case_date']) && !empty($_GET['id'])){

			$id = strip_tags($_GET['id']);
			$ucid = strip_tags($_GET['unique_case_id']);
			$client_email = strip_tags($_GET['client_email']);
			$case_category = strip_tags($_GET['case_category']);
			$case_date = strip_tags($_GET['case_date']);

			// get client
			$select_client_stmt = $conn->prepare(
				"SELECT * FROM customer WHERE email=:email"
			);
			$select_client_stmt->execute(
				[
					'email'	=>		$client_email
				]
			);
			$select_client_rslt = $select_client_stmt->fetchAll();
			foreach($select_client_rslt as $row_client){}

			// get case
			$select_case_stmt = $conn->prepare(
				"SELECT * FROM cases WHERE 
				client_email	=	:client_email AND 
				lawyer_email	=	:lawyer_email AND 
				case_category	=	:case_category AND 
				case_date			=	:case_date"
			);
			$select_case_stmt->execute(
				[
					'client_email'	=>	$client_email,
					'lawyer_email'	=>	$_SESSION['email'],
					'case_category'	=>	$case_category,
					'case_date'			=>	$case_date
				]
			);
			$select_case_rslt = $select_case_stmt->fetchAll();
			foreach($select_case_rslt as $row_case){}
		} else{
			header('location: indexdata.php');
		}

		$select_stmt = $conn->prepare(
			"SELECT * FROM lawyer WHERE 
			email=:email AND 
			phone=:phone"
		);
		$select_stmt->execute(
			[
				'email'	=>	$_SESSION['email'], 
				'phone'	=>	$_SESSION['phone']
			]
		);
		$select_rslt = $select_stmt->fetchAll();
		foreach($select_rslt as $row){}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Advogate- Lawyer Notification Details</title>
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
                                        <img alt="" class="logo-2" src="../images/logo.png" />
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
                                <h1>Notifications</h1>
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
                    <div class="row">
                    <div class="container">
            <div class="card bg-template shadow mt-4 h-190">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <figure class="avatar avatar-60"><img src="../images/user3.png" alt=""></figure>
                        </div>
                        <div class="col pl-0 align-self-center">
                            <h5 class="mb-1"><!-- John Jakson --><?php echo $row_client['fullname']; ?> </h5>
                            <p class="text-mute"><!-- Work, London, UK --> <?php echo $row_client['email']; ?><!--  <small class="float-right text-mute">3 days ago</small> --></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container top-100">
					<div class="card mb-4 shadow">
						<div class="card-body">
							<h5 class="mb-3"><small>Case Request</small></h5>
							<?php
								if($row_case['case_status'] == 'request sent'){
							?>
							<p class="text-secondary">
								<?php
									echo "Client Email: ".$row_case['client_email'];
									echo "<br>";
									echo "Case Category: ".$row_case['case_category'];
									echo "<br>";
									echo "Case Sub-Category: ".$row_case['case_subcategory'];
									echo "<br>";
									echo "Case Query: ".$row_case['case_query'];
								?>
							</p>
							<h5 class="mb-3"><small>Case Details</small></h5>
							<p class="text-secondary text-mute">
								<?php
									echo $row_case['case_detail'];
								?>
							</p>
							<form action="indexdata.php" method="post">
								<input type="hidden" name="id" value="<?php echo $id; ?>">
								<input type="hidden" name="unique_case_id" value="<?php echo $ucid; ?>">
								<input type="hidden" name="client_email" value="<?php echo $row_case['client_email']?>">
								<input type="hidden" name="case_category" value="<?php echo $row_case['case_category']?>">
								<input type="hidden" name="case_date" value="<?php echo $row_case['case_date']?>">
								<div class="row">
									<div class="col">
										<input type="submit" name="submit_accept" class="btn btn-block btn-success btn-rounded" value="accept">
									</div>
									<div class="col">
										<input type="submit" name="submit_decline" class="btn btn-block btn-danger btn-rounded" value="decline">
									</div>
								</div>
							</form>
							<?php
								} elseif($row_case['case_status']=='case active' || $row_case['case_status']=='request declined' || $row_case['case_status']=='case resolved' || $row_case['case_status']=='case updated'){
							?>
							<hr>
							<p class="text-secondary text-mute"> <?php echo "you have already responded to this request" ?> </p>
							<?php
								}
							?>
						</div>
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