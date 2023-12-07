<?php
	include 'db.php';
	session_start();
	
	// check if any exist
	$select_count_rows_stmt = $conn->prepare(
		"SELECT COUNT(*) FROM customer"
	);
	$select_count_rows_stmt->execute();
	$select_count_rows_rslt = $select_count_rows_stmt->fetchAll();
	foreach($select_count_rows_rslt as $row){
		if($row[0] > 0){
			$users_exist = true;
			break;
		} elseif($row[0] == 0){
			$users_exist = false;
		}
	}

	if(isset($_POST['submit'])){

		//check if all fields are set
		if(isset($_POST['fullname'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['password1'])){

			//check if user already exists or not
			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){

				//if any users exist
				if($users_exist == true){

					//select user
					$select_stmt = $conn->prepare(
						"SELECT * FROM customer"
					);
					$select_stmt->execute();
					$select_rslt = $select_stmt->fetchAll();
					foreach($select_rslt as $row){
						if($_POST['email'] == $row['email']){
							$user_exists = true;
							break;
						} elseif($_POST['email'] != $row['email']){
							$user_exists = false;
							continue;
						}
					}

					if(isset($user_exists)){
						if($user_exists == true){
							$display_msg = "User with same email already exists";
						} elseif($user_exists == false){
							if($_POST['password'] === $_POST['password1']){
                                //if user doesnt exist insert into db
                                $insert_stmt = $conn->prepare(
                                    "INSERT INTO customer (
                                        fullname, 
                                        email, 
                                        password, 
                                        phone
                                    ) VALUE (
                                        :fullname, 
                                        :email, 
                                        :password, 
                                        :phone
                                    )"
                                );
                                $insert_stmt->execute(
                                    [
                                        'fullname'	=>	$_POST['fullname'], 
                                        'email'     =>	$_POST['email'], 
                                        'password'  =>	$_POST['password'], 
                                        'phone'     =>	$_POST['phone']
                                    ]
                                );
                                if($insert_stmt == true){
                                    $_SESSION['email'] = $_POST['email'];
                                    $_SESSION['phone'] = $_POST['phone'];
                                    header('location: indexdata.php');
                                } elseif($insert_stmt == false){
                                    $display_msg = "User could not be created due to some Error with database";
                                }
                            } else{
                                $display_msg = "The Password you entered did not match";
                            }
						}
					}
				} elseif($users_exist == false){

					//if user doesnt exist insert into db
					$insert_stmt = $conn->prepare(
						"INSERT INTO customer (
							fullname, email, password, phone
						) VALUE (
							:fullname, :email, :password, :phone
						)"
					);
					$insert_stmt->execute(
						[
							'fullname'	=>	$_POST['fullname'], 
							'email'     =>	$_POST['email'], 
							'password'  =>	$_POST['password'], 
							'phone'     =>	$_POST['phone']
						]
					);
					if($insert_stmt == true){
						$_SESSION['email'] = $_POST['email'];
						$_SESSION['phone'] = $_POST['phone'];
						header('location: indexdata.php');
					} elseif($insert_stmt == false){
						$display_msg = "User could not be created due to some Error with database";
					}
				}
			} elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
				$display_msg = "Invalid email format";
			}
		} else{
			$display_msg = "Please fill the form";
		}
	}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Advogate- User Signup</title>
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
                                <h1>User Signup</h1>
                                <p>Reputation. Respect. Result.</p>
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
											<div class="row no-gutters login-row">
                        <div class="col align-self-center px-3 text-center">
                            <br>
                            <img src="../images/logo-advogate.png" alt="logo" class="logo-small">
                            <br>
                            <br>
                            <h6> Sign Up As User</h6>
                            <?php
                                if(isset($display_msg)){
                                    echo $display_msg;
                                }
                            ?>
                            <span id="msg" style="display: none;"></span>
                            <form class="form-signin mt-3 " method="post" action="signup.php" name="form">
                                <div class="form-group">
                                    <input name="fullname" type="text" id="fullname" class="form-control form-control-lg text-center" placeholder="Full Name" required>
                                </div>
                                <div class="form-group">
                                    <input name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" type="email" id="email" class="form-control form-control-lg text-center" placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <input name="phone" type="tel" id="phone" pattern="[0-9]{4}[0-9]{7}" class="form-control form-control-lg text-center" placeholder="03xx 1234567" required>
                                </div>
                                <div class="form-group">
                                    <input name="password" type="password" pattern=".{8,}" id="inputPassword" class="form-control form-control-lg text-center" placeholder="Password (8 digits)" required>
                                </div>
                                <div class="form-group">
                                    <input name="password1" type="password" pattern=".{8,}" id="confirmPassword" class="form-control form-control-lg text-center" placeholder="Confirm Password (8 digits)" required>
                                </div>
                                <input type='submit' name='submit' value="next" class="btn btn-default btn-lg btn-rounded shadow btn-block">
                                <p class="mt-4 d-block text-secondary">
                                    By clicking register your are agree to the
                                    <a href="javascript:void(0)">Terms and Condition.</a>
                                </p>
                            </form>
                        </div>
                    </div>
											</div>
                        <!-- <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-worker"></i>
                                <div class="text">
                                    <h4>Labor</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-worker"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-medical-sign-alt"></i>
                                <div class="text">
                                    <h4>Medical &amp; Health Care</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-medical-sign-alt"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-mining"></i>
                                <div class="text">
                                    <h4>Mining</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-mining"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-law-order"></i>
                                <div class="text">
                                    <h4>Civil &amp; Criminal</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-law-order"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-group-students"></i>
                                <div class="text">
                                    <h4>Family &amp; Marriage</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-group-students"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-money"></i>
                                <div class="text">
                                    <h4>Corporate &amp; Investment</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-money"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-building"></i>
                                <div class="text">
                                    <h4>Property</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-building"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-bank"></i>
                                <div class="text">
                                    <h4>Banking &amp; Insurance</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-bank"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb30">
                            <div class="feature-box f-boxed style-3 text-center">
                                <i class="id-color icofont-light-bulb"></i>
                                <div class="text">
                                    <h4>Intellectual Property</h4>
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem.
                                </div>
                                <i class="wm icofont-light-bulb"></i>
                                <div class="spacer-single"></div>
                                <a href="#" class="btn-custom btn-black">Read More</a>
                            </div>
                        </div> -->
                    </div>
                </div>
            </section>
        </div>
        <!-- content close -->
        <section class="pt40 pb40 bg-color text-light">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-sm-30 text-lg-left text-sm-center">
                            <h3 class="no-bottom">Hire Your Law Specialist Now!</h3>
                        </div>
                        <div class="col-md-4 text-lg-right rtl-lg-left text-sm-center">
                            <a href="http://localhost/advodesktop/user/signup.php" class="btn-custom btn-black light">Sign Up Now</a>
                        </div>
                    </div>
                </div>
            </section>
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
                                    &copy; Copyright 2020 - Advogate
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