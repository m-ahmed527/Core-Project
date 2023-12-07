<?php 
    include 'db.php';
    session_start();
    
    $show_modal = false;
    if(isset($_SESSION['email'], $_SESSION['phone'])){

			// for user notification
			$select_new_case_request_stmt = $conn->prepare(
					"SELECT COUNT(*) FROM notification WHERE 
					client_email = :client_email AND notification_status=:notification_status"
			);
			$select_new_case_request_stmt->execute(
				[
					'client_email'				=>	$_SESSION['email'],
					'notification_status' =>	'user unread/lawyer read'
				]
			);
			$select_new_case_request_rslt = $select_new_case_request_stmt->fetchAll();
			foreach($select_new_case_request_rslt as $unread_case){}

			//for active cases number
			$select_active_case_stmt = $conn->prepare(
					"SELECT * FROM cases WHERE client_email=:client_email"
			);
			$select_active_case_stmt->execute(
				[
					'client_email'	=>	$_SESSION['email'],
				]
			);
			$select_active_case_rslt = $select_active_case_stmt->fetchAll();
			foreach($select_active_case_rslt as $active_case){}

			if(isset($_POST['pay_submit'])){
				if(!empty($_POST['pay_amount']) && !empty($_POST['pay_case_name']) && !empty($_POST['pay_date']) && !empty($_POST['pay_lawyer_email'])){
					foreach($select_active_case_rslt as $row){
						if($row['unique_case_id'] === $_POST['pay_case_name']){
							$case_there = true;
							break;
						} else {
							$case_there = false;
						}
					}
					if($case_there === true){
						$t_id = $_POST['pay_case_name'];
						$insert_in_payments_stmt = $conn->prepare(
							"INSERT INTO payments (
								case_id, 
								transaction_id, 
								client_email, 
								lawyer_email, 
								amount, 
								currency
							) VALUES (
								:case_id, 
								:transaction_id, 
								:client_email, 
								:lawyer_email,  
								:amount, 
								:currency
							)"
						);
						$insert_in_payments_rslt = $insert_in_payments_stmt->execute(
							[
								'case_id'   			=>  $_POST['pay_case_name'], 
								'transaction_id'	=>  $t_id, 
								'client_email'   	=>  $_SESSION['email'], 
								'lawyer_email'		=>	$_POST['pay_lawyer_email'], 
								'amount'   				=>  $_POST['pay_amount'], 
								'currency'   			=>  'PKR'
							]
						);

						$insert_in_notification_stmt = $conn->prepare(
							"INSERT INTO notification (
								case_id, 
								client_email, 
								lawyer_email, 
								case_status, 
								notification_status, 
								case_date
							) VALUES (
								:case_id, 
								:client_email,  
								:lawyer_email, 
								:case_status, 
								:notification_status,
								:case_date
							)"
						);
						$insert_in_notification_rslt = $insert_in_notification_stmt->execute(
							[
								'case_id'							=>  $_POST['pay_case_name'], 
								'client_email'   			=>  $_SESSION['email'], 
								'lawyer_email'				=>	$_POST['pay_lawyer_email'], 
								'case_status'   			=>  'case payment made', 
								'notification_status' =>  'user read/lawyer unread', 
								'case_date'   				=>  $_POST['pay_date'], 
							]
						);
						if($insert_in_payments_rslt === true && $insert_in_notification_rslt === true){
							$display_msg = 'Payment made Sucessfully';
						} else {
							$display_msg = 'Payment Unsucessfully';
						}
					} elseif($case_there === false){
						$display_msg = "Such case does not exist. ";
					}
				} else {
					$display_msg = "Please fill the form Properly";
				}
			}
			
			$select_payments_stmt = $conn->prepare(
				"SELECT * FROM Payments WHERE client_email=:client_email"
			);
			$select_payments_stmt->execute(
				[
					'client_email'	=>	$_SESSION['email']
				]
			);
			$select_payments_rslt = $select_payments_stmt->fetchAll();

			$select_payments_total_stmt = $conn->prepare(
				"SELECT SUM(amount) FROM payments WHERE client_email=:client_email"
			);
			$select_payments_total_stmt->execute(
				[
					'client_email'	=>	$_SESSION['email']
				]
			);

			$select_user_stmt = $conn->prepare(
				"SELECT * FROM customer WHERE email=:email"
			);
			$select_user_stmt->execute(
				[
					'email'	=>	$_SESSION['email']
				]
			);
			$select_user_rslt = $select_user_stmt->fetchAll();
			foreach($select_user_rslt as $row){}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Advocate- User Wallet</title>
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
                                <h1>Transactions</h1>
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
								<div class="card bg-template shadow mt-4 h-150">
										<div class="card-body">
												<div class="row">
														<div class="col">
															<h3 class="mb-0 font-weight-normal">
															<?php 
																if($select_payments_total_stmt->rowCount() > 0) { 
																	foreach($select_payments_total_stmt as $row){ 
																		echo $row[0]; 
																	} 
																} else { 
																	echo "0"; 
																} 
															?>
																Rs.</h3>
															<p class="text-mute">Total Spent</p>
														</div>
														<h5 class="mt-3">Pay</h5>
														<div class="col-auto">
																<button class="btn btn-default btn-rounded-54 shadow" data-toggle="modal" data-target="#addmoney"><i class="material-icons">add</i></button>
														</div>
												</div>
										</div>
								</div>
							</div>
							<div class="container top-50">
									<div class="card mb-4 shadow">
											<div class="card-body bg-none py-3">
													<div class="row">
															<div class="col text-right">
																	<p>$ 90 <i class="material-icons text-danger vm small">arrow_downward</i><br><small class="text-mute"></small></p>
															</div>
															<div class="col border-left-dotted">
																	<p><i class="material-icons text-success vm small mr-1">arrow_upward</i>$ 105<br><small class="text-mute"></small></p>
															</div>
													</div>
											</div>
									</div>
							</div>
							<div class="container">
								<!-- <input type="text" class="form-control form-control-lg search my-3" placeholder="Search"> -->
								<h5>Transactions</h5>
								<?php
								
									foreach($select_payments_rslt as $row){
										echo '<div class="card shadow-sm border-0 mb-3">
														<div class="card-body">
															<div class="row align-items-center">
																<div class="col-auto pr-0">
																	<div class="avatar avatar-40 no-shadow border-0">
																		<img src="../images/user2.png" alt="">
																	</div>
																</div>
																<div class="col pr-0">
																	<h6 class="font-weight-normal mb-1">'.$row['lawyer_email'].'</h6>
																	<p class="text-mute small text-secondary">'.$row['amount'].' .Rs </p>
																</div>
															</div>
														</div>
													</div>';
									}
								
								?>
								<div class="text-center">
									<?php
										if(isset($display_msg)){
											echo $display_msg;
										}
									?>
								</div>
								</div>
							</div>
						</div>
					</section>
        </div>
				<div class="modal fade" id="addmoney" tabindex="-1" role="dialog" aria-labelledby="addmoenylabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5>Make Payments</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="transactions.php" method="post">
                <div class="modal-body text-center pt-0">
                    <!-- <img src="../images/infomarmation-graphics2.png" alt="logo" class="logo-small"> -->
                    <div class="form-group mt-4">
                    <input type="hidden" name="pay_date" class="form-control form-control-lg text-center" value="<?php echo date("d/m/y").' | '.date("H:i:s"); ?>" required="" autofocus="">
                        <input type="number" name="pay_amount" class="form-control form-control-lg text-center" placeholder="Enter amount" required="" autofocus="">
                    </div>
                    <div class="form-group mt-4">
                        <input type="hidden" name="pay_lawyer_email" class="form-control form-control-lg text-center" value="<?php echo $row_p['lawyer_email']; ?>">
                    </div>
                    <div class="form-group mt-4">
                    <select name="pay_case_name" class="form-control form-control-lg text-center" >
                        <option placeholder="Select your case"> Select your case </option>
                        <?php
													foreach($select_active_case_rslt as $row){
														if($row['case_status'] === 'case active'){
															echo '<option value="'.$row['unique_case_id'].'"> '.$row['case_category'].' - '.$row['lawyer_email'].'</option>';
														}
													}
                        ?>
                    </select>
                    </div>
                    <p class="text-mute">You will be redirected to payment gatway to procceed further. Enter amount in PKR.</p>
                </div>
                <div class="modal-footer border-0">
                    <!-- <button type="button" class="btn btn-default btn-lg btn-rounded shadow btn-block" class="close" data-dismiss="modal" >Next</button> -->
                    <input type="submit" class="btn btn-default btn-lg btn-rounded shadow btn-block" value="Next" name="pay_submit">
                </div>
                </form>
            </div>
        </div>
    </div>
        <!-- content close -->
        <!-- <section class="pt40 pb40 bg-color text-light">
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
            </section> -->
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
									<input type="hidden" name="case_date" value="<?php echo date('d/m/y')." | ".date("H:i:s"); ?>">
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
											<option value="Real Estate Law">Real Estate Law</option>
											<option value="Banking Law">Banking Law</option>
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

					<div class="modal fade" id="listmodal" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header border-0">
									<h5>Recommended Lawyer List</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body pt-0">
									<?php 
										if(isset($select_lawyer_rslt) && !empty($_SESSION['case_detail']) && !empty($_SESSION['case_location']) && !empty($_SESSION['case_date']) && !empty($_SESSION['case_category']) && !empty($_SESSION['case_subcategory']) && !empty($_SESSION['case_query'])){
											// price for case category civil law
											if($_SESSION['case_query'] == "Divorce/Khula"){
												$price_range = "20000 - 50000";
												$price = 3000;
											} elseif($_SESSION['case_query'] == "Custody Matter"){
												$price_range = "20000 - 50000";
												$price = 4000;
											} elseif($_SESSION['case_query'] == "Maintenance"){
												$price_range = "20000 - 50000";
												$price = 5000;
											} elseif($_SESSION['case_query'] == "Guardianship"){
												$price_range = "20000 - 50000";
												$price = 6000;
											}

											if($_SESSION['case_query'] == "Defamation Damage"){
												$price_range = "35000 - 60000";
												$price = 8000;
											} elseif($_SESSION['case_query'] == "Property Damage"){
												$price_range = "35000 - 70000";
												$price = 9000;
											} elseif($_SESSION['case_query'] == "Compenstory"){
												$price_range = "45000 - 90000";
												$price = 10000;
											}

											if($_SESSION['case_subcategory'] == "Trial"){
												if($_SESSION['case_query'] == "Murder"){
													$price_range = "35000 - 90000";
													$price = 13000;
												} elseif($_SESSION['case_query'] == "Narcotics/Drugs"){
													$price_range = "40000 - 90000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "NAB"){
													$price_range = "60000 - 110000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Anticorruption"){
													$price_range = "60000 - 120000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Bounce Cheque"){
													$price_range = "35000 - 60000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Theft"){
													$price_range = "35000 - 60000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Fraud/Cheating"){
													$price_range = "35000 - 60000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Illegal Possesion"){
													$price_range = "35000 - 60000";
														$price = 13000;
												}
											} elseif($_SESSION['case_subcategory'] == "Bail"){
												if($_SESSION['case_query'] == "Murder"){
													$price_range = "25000 - 75000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Narcotics/Drugs"){
													$price_range = "25000 - 75000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "NAB"){
													$price_range = "50000 - 100000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Anticorruption"){
													$price_range = "13000 - 15000";
													$price = 13000;
												} elseif($_SESSION['case_query'] == "Bounce Cheque"){
													$price_range = "25000 - 50000";
													$price = 13000;
												} elseif($_SESSION['case_query'] == "Theft"){
													$price_range = "25000 - 50000";
														$price = 13000;
												} elseif($_SESSION['case_query'] == "Kidnapping"){
													$price_range = "25000 - 50000";
													$price = 13000;
												} elseif($_SESSION['case_query'] == "Fraud/Cheating"){
													$price_range = "25000 - 50000";
													$price = 13000;
												}
											}

											if($_SESSION['case_query'] == "Company Registration"){
												$price_range = "30000 - 50000";
												$price = 18000;
											} elseif($_SESSION['case_query'] == "Trademark"){
												$price_range = "10000 - 25000";
												$price = 19000;
											} elseif($_SESSION['case_query'] == "Copyright"){
												$price_range = "10000 - 25000";
												$price = 20000;
											} elseif($_SESSION['case_query'] == "Patent"){
												$price_range = "10000 - 25000";
												$price = 21000;
											}

											if($_SESSION['case_query'] == "Depositing Rent in Court"){
												$price_range = "10000 - 30000";
												$price = 23000;
											} elseif($_SESSION['case_query'] == "Rent Case Trial"){
												$price_range = "20000 - 50000";
												$price = 24000;
											} elseif($_SESSION['case_query'] == "Stoppage of Ammenities"){
												$price_range = "10000 - 30000";
												$price = 25000;
											}

											// price for case category criminal law
											if($_SESSION['case_query'] == "Special Court Offence in Bank"){
												$price_range = "30000 - 60000";
												$price = 28000;
											} elseif($_SESSION['case_query'] == "Load Recovery"){
												$price_range = "50000 - 80000";
												$price = 29000;
											} elseif($_SESSION['case_query'] == "Criminal Complaint"){
												$price_range = "30000 - 60000";
												$price = 30000;
											}
											foreach($select_lawyer_rslt as $row_lawyer){
									?>
										<div class="row">
											<div class="col-12 px-0">
												<ul class="list-group list-group-flush border-top border-bottom">
													<li class="list-group-item">
														<div class="row align-items-center">
															<div class="col-auto pr-0">
																<div class="avatar avatar-50 no-shadow border-0 mb-3">
																	<img src="../images/user2.png" alt="">
																</div>
															</div>
															<div class="col-auto">
																<h6 class="font-weight-normal mb-1">
																	<?php echo $row_lawyer['fullname']; ?>
																</h6>
																<p class="text-mute small text-secondary">
																	<?php echo $row_lawyer['category']; ?>
																</p>
															</div>
															<div class="col-auto">
																<h6 class="text-success">Rs. <?php echo $price_range; ?></h6>
																<?php
																	// if($row_lawyer['enroll_type'] == "HC"){
																	// 	$rating = 1;
																	// 	if($row_lawyer['degree'] == "LLM"){
																	// 		$rating += 1;
																	// 		if($row_lawyer['lawyer_exp'] == "1 years"){
																	// 			$rating += 0;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "2 years"){
																	// 			$rating += 0;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "3 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "4 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "5 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "6 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "7 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "8 years"){
																	// 			$rating += 2;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "9 years"){
																	// 			$rating += 2;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "10 years"){
																	// 			$rating += 2;

																	// 		}
																	// 	} elseif($row_lawyer['degree'] == "LLB"){
																	// 		$rating += 0;
																	// 		if($row_lawyer['lawyer_exp'] == "1 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "2 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "3 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "4 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "5 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "6 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "7 years"){
																	// 			$rating += 1;

																	// 			$rating += 2;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "9 years"){
																	// 			$rating += 2;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "10 years"){
																	// 			$rating += 2;

																	// 		}
																	// 	}
																	// } elseif($row_lawyer['enroll_type'] == "LC"){
																	// 	$rating = 0;
																	// 	if($row_lawyer['degree'] == "LLM"){
																	// 		$rating += 1;
																	// 		if($row_lawyer['lawyer_exp'] == "1 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "2 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "3 years"){
																	// 			$rating += 0;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "4 years"){
																	// 			$rating += 1;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "5 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "6 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "7 years"){
																	// 			$rating += 1;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "8 years"){
																	// 			$rating += 2;

																	// 		} elseif($row_lawyer['lawyer_exp'] == "9 years"){
																	// 			$rating += 2;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "10 years"){
																	// 			$rating += 2;
																	// 		}
																	// 	} elseif($row_lawyer['degree'] == "LLB"){
																	// 		$rating += 0;
																	// 		if($row_lawyer['lawyer_exp'] == "1 years"){
																	// 			$rating += 0;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "2 years"){
																	// 			$rating += 0;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "3 years"){
																	// 			$rating += 0;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "4 years"){
																	// 			$rating += 1;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "5 years"){
																	// 			$rating += 1;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "6 years"){
																	// 			$rating += 1;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "7 years"){
																	// 			$rating += 1;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "8 years"){
																	// 			$rating += 2;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "9 years"){
																	// 			$rating += 2;
																	// 		} elseif($row_lawyer['lawyer_exp'] == "10 years"){
																	// 			$rating += 2;
																	// 		}
																	// 	}
																	// }
																	// if($rating == 1){
																	// 	echo '<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star "></span>
																	// 	<span class="fa fa-star "></span>
																	// 	<span class="fa fa-star"></span>
																	// 	<span class="fa fa-star"></span>';
																	// 	$price += 500;
																	// } elseif($rating == 2){
																	// 	echo '<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star "></span>
																	// 	<span class="fa fa-star"></span>
																	// 	<span class="fa fa-star"></span>';
																	// 	$price += 1000;
																	// } elseif($rating == 3){
																	// 	echo '<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star"></span>
																	// 	<span class="fa fa-star"></span>';
																	// 	$price += 1500;
																	// } elseif($rating == 4){
																	// 	echo '<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star"></span>';
																	// 	$price += 2000;
																	// } elseif($rating == 5){
																	// 	echo '<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>
																	// 	<span class="fa fa-star checked"></span>';
																	// 	$price += 2500;
																	// }
																?>
															</div>
															<div class="col-auto">
																<form action="lawyer_profile.php" method="post" style="display: inline;">
																	<input name="lawyer_email" type="hidden" value="<?php echo $row_lawyer['email']; ?>" />
																	<input name="case_category" type="hidden" value="<?php echo $_SESSION['case_category']; ?>" />
																	<input name="case_subcategory" type="hidden" value="<?php echo $_SESSION['case_subcategory']; ?>" />
																	<input name="case_query" type="hidden" value="<?php echo $_SESSION['case_query']; ?>" />
																	<input name="case_detail" type="hidden" value="<?php echo $_SESSION['case_detail']; ?>" />
																	<input name="case_date" type="hidden" value="<?php echo $_SESSION['case_date']; ?>" />
																	<input name="case_location" type="hidden" value="<?php echo $_SESSION['case_location']; ?>" />
																	<input name="case_price" type="hidden" value="<?php echo $price; ?>" />
																	<input type="submit" name="lawyer_submit" value="request" class="mb-2 btn btn-default"/>
																</form>
															</div>
														</div>
													</li>
												</ul>
											</div>
										</div>
									<?php
											}
										} else{
									?>
								</div>
									<div class="modal-body pt-0">
										<div class="row">
											<div class="col-12 px-0">
												<ul class="list-group list-group-flush border-top border-bottom">
													<li class="list-group-item">
														<div class="row align-items-center">
															<h5>No Results</h5>
														</div>
													</li>
												</ul>
											</div>
										</div>
									</div>
								<?php
									}
								?>
								<div class="modal-footer border-0">
									<!-- <button type="button" class="btn btn-default btn-lg btn-rounded shadow btn-block" class="close" data-dismiss="modal">Next</button> -->
								</div>
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
		<script>
		$(document).ready(function(){
			$("#case_subcategory").hide();
			$("#case_query").hide();

			$("#case_category").on("change", function(){

				$("#case_subcategory").show();
				$("#case_query").hide();

				var cc_sel_val = $("#case_category").val();

				if(cc_sel_val === "Civil Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Family Law'> Family Law </option>";
					var csc_opt2 = "<option value='Damages'> Damages </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}
				} else if(cc_sel_val === "Criminal Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Bail'> Bail </option>";
					var csc_opt2 = "<option value='Trial'> Trial </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}
				} else if(cc_sel_val === "Corporate Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Company Law'> Company Law </option>";
					var csc_opt2 = "<option value='IPO'> IPO </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}
				} else if(cc_sel_val === "Real Estate"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Rent'> Rent </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1);
					}
				} else if(cc_sel_val === "Banking Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Bank Offence'> Bank Offence </option>";
					var csc_opt2 = "<option value='Recovery'> Recovery </option>";
					var csc_opt3 = "<option value='Criminal Offence'> Criminal Offence </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2, csc_opt3);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2, csc_opt3);
					}
				}
			});

			$("#case_subcategory").on("change", function(){

				$("#case_query").show();

				var csc_sel_val = $("#case_subcategory").val();

				if(csc_sel_val === "Family Law"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Divorce/Khula'> Divorce/Khula </option>";
					var cq_opt2 = "<option value='Custody Matter'> Custody Matter </option>";
					var cq_opt3 = "<option value='Maintenance'> Maintenance </option>";
					var cq_opt4 = "<option value='Guardianship'> Guardianship </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4);
					}
				}	else if(csc_sel_val === "Damages"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Defamation Damage'> Defamation Damage </option>";
					var cq_opt2 = "<option value='Property Damage'> Property Damage </option>";
					var cq_opt3 = "<option value='Compensatory'> Compensatory </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}
				}	if(csc_sel_val === "Bail"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Murder'> Murder </option>";
					var cq_opt2 = "<option value='Narcotics/Drugs'> Narcotics/Drugs </option>";
					var cq_opt3 = "<option value='NAB'> NAB </option>";
					var cq_opt4 = "<option value='Anticorruption'> Anticorruption </option>";
					var cq_opt5 = "<option value='Bounce Cheque'> Bounce Cheque </option>";
					var cq_opt6 = "<option value='Theft'> Theft </option>";
					var cq_opt7 = "<option value='Kidnapping'> Kidnapping </option>";
					var cq_opt8 = "<option value='Fraud/Cheating'> Fraud/Cheating </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					}
				}	if(csc_sel_val === "Trial"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Murder'> Murder </option>";
					var cq_opt2 = "<option value='Narcotics'> Narcotics </option>";
					var cq_opt3 = "<option value='NAB'> NAB </option>";
					var cq_opt4 = "<option value='Anticorruption'> Anticorruption </option>";
					var cq_opt5 = "<option value='Bounce Cheque'> Bounce Cheque </option>";
					var cq_opt6 = "<option value='Theft'> Theft </option>";
					var cq_opt7 = "<option value='Kidnapping'> Kidnapping </option>";
					var cq_opt8 = "<option value='Fraud/Cheating'> Fraud/Cheating </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					}
				}	if(csc_sel_val === "Company Law"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Company Registration'> Company Registration </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}
				}	if(csc_sel_val === "IPO"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Trademark'> Trademark </option>";
					var cq_opt2 = "<option value='Copy Right'> Copy Right </option>";
					var cq_opt3 = "<option value='Patent'> Patent </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}
				}	if(csc_sel_val === "Rent"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Depositing Rent in Court'> Depositing Rent in Court </option>";
					var cq_opt2 = "<option value='Rent Case Trial'> Rent Case Trial </option>";
					var cq_opt3 = "<option value='Stoppage of Ammenities'> Stoppage of Ammenities </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}
				}	if(csc_sel_val === "Bank Offence"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Special Court Offence in Bank'> Special Court Offence in Bank </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}
				}	if(csc_sel_val === "Recovery"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Loan Recovery'> Loan Recovery </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}
				}	if(csc_sel_val === "Criminal Offence"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Criminal Complaint'> Criminal Complaint </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}
				}	
			});
		});
		</script>

		<?php
			if(isset($show_listmodal)) {
				if($show_listmodal === true) {
					?>
						<script> 
							$('#listmodal').modal('show');
						</script>
					<?php
				}
			}
		?>
</body>

</html>
<?php
	} else{
			header('location: log-out.php');
	}
?>
		
		