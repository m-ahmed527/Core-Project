<?php

	include 'db.php';
	session_start();
	function extractKeyWords($string) {
	  mb_internal_encoding('UTF-8');
	  $stopwords = array();
	  $string = preg_replace('/[\pP]/u', '', trim(preg_replace('/\s\s+/iu', '', mb_strtolower($string))));
	  $matchWords = array_filter(explode(' ',$string) , function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) <= 2 || is_numeric($item));});
	  $wordCountArr = array_count_values($matchWords);
	  arsort($wordCountArr);
	  return array_keys(array_slice($wordCountArr, 0, 10));
	}
	function finddata($keyword){
		global $conn;
		  $sql = "SELECT customer.id,lawyer.id as lawyer_id,
		  		  lawyer.category,lawyer.category2,lawyer.category3,lawyer.category4,lawyer.category5,lawyer.category6,
		  		  ratings.rating,
		  		  customer.fullname as customer_name,
		  		  lawyer.fullname as lawyer_name
				  FROM customer
				  INNER JOIN ratings on ratings.customer_id = customer.id
				  INNER JOIN lawyer ON lawyer.id = ratings.lawyer_id
				  #WHERE lawyer.category like '%$keyword%'";
					//echo "<br>";
		return $conn->query($sql)->fetchAll();
	}
	function findlaywer($id){
		global $conn;
		  $sql = "SELECT* FROM lawyer WHERE lawyer.id IN($id)";
		return $conn->query($sql)->fetchAll();
	}
	function findalllaywer(){
		global $conn;
		 $sql = "SELECT* FROM lawyer";
		 $result =$conn->query($sql)->fetchAll();
		 foreach ($result as $value) {
		 	$arr[$value['category']] = $value;
		 }
		 //var_dump($arr);
		 //$result=array_unique($arr);
		return $arr;
	}
	function manualsearch($type,$location){
		global $conn;
		 $sql = "SELECT* FROM lawyer where address_line_1 like '$location' and (category like '%$type%' or category2 like '%$type%' or category3 like '%$type%' or category4 like '%$type%' or category5 like '%$type%' or category6 like '%$type%')
		 ";

		 $result =$conn->query($sql)->fetchAll();
		return $result;
	}
	$show_listmodal = false;
	$search_modal = false;
	$show_casemodal = false;
		if(isset($_SESSION['email'], $_SESSION['phone'])){

			if(isset($_POST['lawyer_submit'])){
				if(!empty($_POST['lawyer_email']) && !empty($_POST['lawyer_location']) && !empty($_POST['lawyer_category'])){

					$show_casemodal = true;
				} 
			}
			if(isset($_POST['searchsubmitmanual'])){
				$result = manualsearch($_POST['casetype'],$_POST['location']);
				$select_lawyers_rslt=$result;
				$mainkeyword=$_POST['casetype'];
				$search_modal=true;
			}
			//Mr mtk working start
			if(isset($_POST['searchsubmit'])){
			//echo "<pre>";var_dump($_POST);die;
				$keywords = extractKeyWords($_POST['searchstr1']);
				//echo"<pre>";var_dump($keywords);
				$arry=array();
				foreach ($keywords as $keyword) {
					$result = finddata($keyword);
					if(!empty($result) ){
						$mainkeyword=$keyword;
						// echo "<pre>";	var_dump($result);
						// echo "<br>";
						// echo "#############";
						foreach ($result as $json ) {
							//echo "<pre>";var_dump($json);
							//echo "#########";
							//$template = "{'".$json['customer_name']."':{'";

							//$template.="'}";	
							$arry[$json['customer_name'] ][]=[
								$json['lawyer_id'] => $json['lawyer_id'].':'.$json['rating']
							];
						}
					}
				}
				$template='';
				$count=0;
				//echo "<pre>";var_dump($arry);
				foreach ($arry as $key => $value) {
					if($count==0){
						$template.= "{";
						$template.= "'".$key."':{";
					}else{
						$template.= ",'".$key."':{";
					}
					//echo $count.'two';
					$count++;
						$countofvalue = count($value);
						//echo $countofvalue;
						//echo"<br>";
						$countstarter=1;
						//echo "<pre>"; var_dump($value);die;
						foreach ($value as $idies) {
							//echo "here". rand();
							//echo $countstarter.'here';
							//echo "<br>";
							//echo "<pre>";var_dump($idies);
							if($countstarter != $countofvalue){

							 $template.= implode("|",$idies).',';
							 //echo "<br>";
							}else{
								$template.= implode("|",$idies);
							}
							$countstarter++;
						}
						$template.="}";
				}
			//echo $template;
			//die;
			//echo "<pre>";	var_dump($arry);die;
			// encode array to json
			//	echo $json = json_encode($arry);
			//die;	

				//write json to file
				if (file_put_contents("python/data.py", 'dataset='.$template.'}')){
				   // echo "\nJSON file created successfully...";
				}
				else {
				 //   echo "Oops! Error creating json file...";
				}
				///collabritive
				$output = shell_exec(' python python/collab-filtering.py'); 
				  //echo 'here';var_dump($output);
				// Display the list of all file 
				// and directory 
				//[99,787]
				$output=str_replace("[", "", $output);
				$output=str_replace("]", "", $output);
				try{
					$select_lawyers_rslt=findlaywer($output);

				}catch(Exception $e ){
						$select_lawyers_rslt='';
				}

				$search_modal=true;

				//echo "<pre>"; var_dump($resultforlawyer);

				//die;
				// if(isset($_POST['searchoption'])){
				// 	if($_POST['searchoption'] === "name"){
				// 		if(isset($_POST['searchstr1'])){
				// 			$searchstr = '%'.$_POST['searchstr1'].'%';
				// 			// $display_msg = $searchstr;
				// 			$select_lawyers_stmt = $conn->prepare(
				// 			"SELECT * FROM lawyer WHERE fullname LIKE :fullname"
				// 			);
				// 			$select_lawyers_stmt->execute(
				// 			[
				// 			'fullname'	=>	$searchstr
				// 			]
				// 			);
				// 			$select_lawyers_rslt = $select_lawyers_stmt->fetchAll();
				// 			$search_modal = true;
				// 		}
				// 	} elseif($_POST['searchoption'] === "email"){
				// 		if(isset($_POST['searchstr1'])){
				// 			$searchstr = '%'.$_POST['searchstr1'].'%';
				// 			// $display_msg = $searchstr;
				// 			$select_lawyers_stmt = $conn->prepare(
				// 			"SELECT * FROM lawyer WHERE email LIKE :email"
				// 			);
				// 			$select_lawyers_stmt->execute(
				// 			[
				// 			'email'	=>	$searchstr
				// 			]
				// 			);
				// 			$select_lawyers_rslt = $select_lawyers_stmt->fetchAll();
				// 			$search_modal = true;
				// 		}
				// 	} elseif($_POST['searchoption'] === "category"){
				// 		if(isset($_POST['searchstr2'])){
				// 			$searchstr = '%'.$_POST['searchstr2'].'%';
				// 			// $display_msg = $searchstr;
				// 			$select_lawyers_stmt = $conn->prepare(
				// 			"SELECT * FROM lawyer WHERE category LIKE :category"
				// 			);
				// 			$select_lawyers_stmt->execute(
				// 			[
				// 			'category'	=>	$searchstr
				// 			]
				// 			);
				// 			$select_lawyers_rslt = $select_lawyers_stmt->fetchAll();
				// 			$search_modal = true;
				// 		}
				// 	}
				// }
			}

			// Case details when submitted, are processed here
			if(isset($_POST['case_submit'])){
				if(!empty($_POST['case_detail']) && !empty($_POST['case_location']) && !empty($_POST['case_category']) && !empty($_POST['case_date'] && !empty($_POST['case_subcategory']) && !empty($_POST['case_query']))
					){

						$_SESSION['case_detail']			= $_POST['case_detail'];
						$_SESSION['case_location']		= $_POST['case_location'];
						$_SESSION['case_category']		= $_POST['case_category'];
						$_SESSION['case_subcategory'] = $_POST['case_subcategory'];
						$_SESSION['case_query']				= $_POST['case_query'];
						$_SESSION['case_date']				= $_POST['case_date'];

						//fetch lawyer by city and category
						$select_lawyer_stmt = $conn->prepare(
							"SELECT * FROM lawyer WHERE 
							city=:city AND 
							category=:category"
						);
						$select_lawyer_stmt->execute(
							[
								'city'			=>	$_POST['case_location'],
								'category'	=>	$_POST['case_category']
							]
						);
						$select_lawyer_rslt = $select_lawyer_stmt->fetchAll();

						$show_listmodal = true;
						
				} elseif(empty($_POST['case_detail']) || empty($_POST['case_location']) || empty($_POST['case_category']) || !empty($_POST['case_date'])){
					$display_msg = "Required fields are empty";
				}
			}

			// Hire Requset recieved here from lawyer_profile.php 
			if(isset($_POST['hire_submit'], $_POST['lawyer_email'])){

				$select_case_stmt = $conn->prepare(
					"SELECT * FROM cases WHERE 
					client_email=:client_email"
				);
				$select_case_stmt->execute(
					[
						'client_email'=>$_SESSION['email'], 
					]
				);
				$select_case_rslt = $select_case_stmt->fetchAll();

				// Check if case already exists
				$case_already = null;
				
				foreach($select_case_rslt as $row_case){
					if($row_case['case_status'] == 'request sent' || $row_case['case_status'] == 'request accepted' || $row_case['case_status'] == 'case active'){
						if($row_case['case_date'] == $_POST['case_date'] && $row_case['case_location'] == $_POST['case_location'] && $row_case['case_category'] == $_POST['lawyer_category'] && $row_case['lawyer_email'] == $_POST['lawyer_email']){
							$case_already = true;
						} else {
							// If case already exists
							$case_already = false;
						}
					}
				}

				// If case does not exist
				if($case_already == false){
					$case_unique_id = uniqid();
					$insert_lawyerforhire_stmt = $conn->prepare(
						"INSERT INTO cases (
							unique_case_id, 
							client_email, 
							lawyer_email, 
							case_category, 
							case_subcategory, 
							case_query, 
							case_price_range, 
							case_date, 
							case_location, 
							case_detail, 
							case_status, 
							notification_status
						) VALUES (
							:unique_case_id, 
							:client_email, 
							:lawyer_email, 
							:case_category, 
							:case_subcategory, 
							:case_query, 
							:case_price_range, 
							:case_date, 
							:case_location, 
							:case_detail, 
							:case_status, 
							:notification_status
						)"
					);
					$insert_lawyerforhire_rslt = $insert_lawyerforhire_stmt->execute(
						[ 
							'unique_case_id'			=>	$case_unique_id, 
							'client_email'				=>	$_SESSION['email'],
							'lawyer_email'				=>	$_POST['lawyer_email'],
							'case_category'				=>	$_SESSION['case_category'],
							'case_subcategory'		=>	$_POST['case_subcategory'],
							'case_query'					=>	$_POST['case_query'],
							'case_price_range'		=>	$_POST['case_price'],
							'case_date'						=>	$_SESSION['case_date'],
							'case_location'				=>	$_SESSION['case_location'],
							'case_detail'					=>	$_SESSION['case_detail'],
							'case_status'					=>	'request sent',
							'notification_status' =>	''
						]
					);

					$insert_into_track_cases_stmt = $conn->prepare(
						"INSERT INTO track_cases (
							case_id, 
							client_email, 
							lawyer_email, 
							case_date, 
							case_detail, 
							case_status
						) VALUES (
							:case_id, 
							:client_email, 
							:lawyer_email, 
							:case_date, 
							:case_detail, 
							:case_status
						)"
					);
					$insert_into_track_cases_rslt = $insert_into_track_cases_stmt->execute(
						[
							'case_id'			=>	$case_unique_id,
							'client_email'=>	$_SESSION['email'],
							'lawyer_email'=>	$_POST['lawyer_email'],
							'case_date'		=>	$_SESSION['case_date'],
							'case_detail'	=>	$_SESSION['case_detail'],
							'case_status'	=>	'request sent'
						]
					);

					$insert_for_notification_stmt = $conn->prepare(
						"INSERT INTO notification (
							case_id,
							client_email, 
							lawyer_email, 
							case_status, 
							notification_status,
							case_category, 
							case_date
						) VALUES (
							:case_id,
							:client_email, 
							:lawyer_email, 
							:case_status,
							:notification_status,
							:case_category,
							:case_date
						)"
					);
					$insert_for_notification_rslt = $insert_for_notification_stmt->execute(
						[
							'case_id'							=>	$case_unique_id,
							'client_email'				=>	$_SESSION['email'],
							'lawyer_email'				=>	$_POST['lawyer_email'],
							'case_status'					=>	'request sent',
							'notification_status'	=>	'user read/lawyer unread',
							'case_category'				=>	$_SESSION['case_category'],
							'case_date'						=>	$_SESSION['case_date']
						]
					);
					if($insert_lawyerforhire_rslt == true && $insert_into_track_cases_rslt == true && $insert_for_notification_rslt == true){
						$display_msg = "Request Sent to Lawyer";
					} elseif($insert_lawyerforhire_rslt == false || $insert_into_track_cases_rslt == false || $insert_for_notification_rslt == false){
						$display_msg = "Request Count not be Sent to Lawyer";
					}
				} elseif($case_already == true){
					$display_msg = "Same case request has already been submitted today";
				}
			}

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
				"SELECT COUNT(*) FROM cases WHERE 
				client_email = :client_email AND 
				case_status = :case_status"
			);
			$select_active_case_stmt->execute(
				[
					'client_email'	=>	$_SESSION['email'],
					'case_status'		=>	'case active'
				]
			);
			$select_active_case_rslt = $select_active_case_stmt->fetchAll();
			foreach($select_active_case_rslt as $active_case){}

			//for resolved cases number
			$select_resolved_case_stmt = $conn->prepare(
				"SELECT COUNT(*) FROM cases WHERE 
				client_email = :client_email AND 
				case_status = :case_status"
			);
			$select_resolved_case_stmt->execute(
				[
					'client_email'	=>	$_SESSION['email'],
					'case_status'		=>	'case resolved'
				]
			);
			$select_resolved_case_rslt = $select_resolved_case_stmt->fetchAll();
			foreach($select_resolved_case_rslt as $resolved_case){}

			$select_user_stmt = $conn->prepare(
				"SELECT * FROM customer WHERE
				email=:email"
			);
			$select_user_stmt->execute(
				[
					'email'=>$_SESSION['email']
				]
			);
			$select_user_rslt = $select_user_stmt->fetchAll();
			foreach($select_user_rslt as $row){}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Advogate- User Dashboard</title>
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
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                                <h1>User Dashboard</h1>
                                <!-- <p>Reputation. Respect. Result.</p> -->
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- section close -->
            <section>
							<div class="containter">
								<div class="row">
									<div class="col mb-4" style="text-align-last:center;">
										<button class="btn btn-custom" data-toggle="modal" data-target="#searchmodal" >Automatic Search <i class="material-icons">search</i></button>
										<button class="btn btn-custom" data-toggle="modal" data-target="#searchmodalmanual" >Manual Search <i class="material-icons">search</i></button>
									</div>
									
								</div>
							</div>
                <div class="container">
                    <div class="row">
											<div class="container">
												<div class="card bg-template shadow mt-4 h-100">
													<div class="card-body">
														<div class="row">
															<div class="col-auto">
																<figure class="avatar avatar-60"><img src="../images/user1.png" alt="profile"></figure>
															</div>
															<div class="col pl-0 align-self-center">
																<h5 class="mb-1"><!-- Ammy Jahnson -->
																	<?php echo $row['fullname']; ?>
																</h5>
																<p class="text-mute small"><!-- Work, London, UK --> 
																<?php echo $row['email']; ?>
																</p>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="container">
												<div class="card bg-template mt-4 h-100">
													<div class="card-body">
														<div class="row">
															<div class="col align-self-center">
																<h4 class="mb-0 font-weight-normal"><!-- Ammy Jahnson -->Case Status</h4>
																<!-- <p class="text-mute small">Work, London, UK</p> -->
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="container">
												<div class="card mb-4 shadow">
													<div class="card-body border-bottom">
														<div class="row">
															<div class="col align-self-center">
																<div class="col" onclick="location.href='active_case_page.php'">
																	<h5 class="mb-0 font-weight-normal">
																		Active Cases (
																			<?php
																				if(isset($active_case[0])){
																					echo $active_case[0];
																				}
																			?>
																		)
																	</h5>
																</div>
																<?php
																	if(isset($resolved_case[0])){
																		if($resolved_case[0] != 0){
																			echo '<hr>
																		<div class="col" onclick="location.href=\'resolved_case_page.php\'">
																			<h5 class="mb-0 font-weight-normal">Resolved Cases ('.$resolved_case[0].')</h5>
																		</div>';
																		}
																	}
																?>
															</div>
															<!-- <div class="col-auto align-self">
																	<button class="btn btn-default btn-rounded-54 shadow" data-toggle="modal" data-target="#addmoney"><i class="material-icons">add</i></button>
															</div> -->
														</div>
													</div>
												</div>
												<div class="row">
														<div class="col text-center">
																<button class="btn btn-default btn-lg rounded-0 shadow" data-toggle="modal" data-target="#casemodal">Add New Case
																		<i class="material-icons">add</i>
																</button>
														</div>
												</div>
												<div class="row">
													<div class="col text-center mt-4">
														<h6>
															<?php 
																if(isset($display_msg)){
																	echo $display_msg;
																}
															?>
														</h6>
													</div>
												</div>
											</div>
                    </div>
                </div>
            </section>
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
            <div class="container">
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

				<div class="modal fade" id="searchmodal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
							<div class="modal-content shadow">
									<div class="modal-header">
											<h5 class="header-title mb-0">Search</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">×</span>
											</button>
									</div>
									<div class="modal-body">
											<form action="indexdata.php" method="post">
                        			<div class="form-group">
													<input type="text" class="form-control form-control-lg text-center" name="searchstr1" id="searchstr">
													<select class="form-control form-control-lg text-center" name="searchstr2" style="display: none" id="searchselect">
														<option value="">Select Case Category</option selected>
														<option value="Civil Law">Civil Law</option>
														<option value="Criminal Law">Criminal Law</option>
														<option value="Corporate Law">Corporate Law</option>
														<option value="Real Estate Law">Real Estate Law</option>
														<option value="Banking Law">Banking Law</option>
													</select>
												</div>
												<div class="form-group">
													<input type="submit" class="btn btn-warning btn-block btn-roundedt" name="searchsubmit" value="submit search string">
												</div>
											</form>
											<!-- <br> -->
									</div>
							</div>
					</div>
				</div>

					<div class="modal fade" id="searchmodalmanual" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
							<div class="modal-content shadow">
									<div class="modal-header">
											<h5 class="header-title mb-0">Search</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">×</span>
											</button>
									</div>
									<div class="modal-body">
											<form action="indexdata.php" method="post">
                        			<div class="form-group">
												<label>Case Type</label>
													<select class="form-control" name="casetype"  id="searchselectmanual">
														<?php foreach (findalllaywer() as $value) {?>
															<option value="<?php echo $value['category'];?>"><?php echo $value['category'];?></option selected>
													
														<?php } ?>
														
													</select>
												<br>
												<label>Location</label>
												<select class="form-control" name="location">
														<?php foreach (findalllaywer() as $value) {?>
															<option value="<?php echo $value['address_line_1'];?>"><?php echo $value['address_line_1'];?></option selected>
													
														<?php } ?>
														
													</select>

													<label>Ratings</label>
													<input class="form-control myinput" type="checkbox" name="ratings" value="1">
												</div>
												<div class="form-group">
													<input type="submit" class="btn btn-warning btn-block btn-roundedt" name="searchsubmitmanual" value="submit search string">
												</div>
											</form>
											<!-- <br> -->
									</div>
							</div>
					</div>
				</div>

				<div class="modal fade" id="searchresultmodal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
						<div class="modal-content shadow">
								<div class="modal-header">
										<h5 class="header-title mb-0">Search</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">×</span>
										</button>
								</div>
								<div class="modal-body text-center pr-4 pl-4">
										<!-- <h5 class="my-3">Ananya Johnsons</h5> -->
										<?php
											//if(isset($select_lawyers_rslt)){
												//echo '1';die;
												//if(count($select_lawyers_stmt) > 0){
													if($select_lawyers_rslt ==''){
														echo "No results";
														
													}else{
													foreach($select_lawyers_rslt as $row){

		if(preg_match("/{$mainkeyword}/i", $row['category']) ||   preg_match("/{$mainkeyword}/i", $row['category2']) || preg_match("/{$mainkeyword}/i", $row['category3']) || preg_match("/{$mainkeyword}/i", $row['category4'])  || preg_match("/{$mainkeyword}/i", $row['category5'])  || preg_match("/{$mainkeyword}/i", $row['category6'])) {

														echo '<div class="containeroflaywer text-center mt-4 mb-4">
																		<div class="row align-items-center">
																			<div class="col-auto pr-0">
																				<div class="avatar avatar-60 no-shadow border-0">
																					<img src="../lawyer/'.$row['profile_pic'].'" alt="">
																				</div>
																			</div>
																			<div class="col">
																				<h6 class="font-weight-normal mb-1">'.$row['fullname'].'</h6>
																				<p class="text-mute small text-secondary">'.$row['category'].'yer</p>
																				<p class="text-mute small text-secondary">'.$row['address_line_1'].'yer</p>
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-auto">
																				<form action="indexdata.php" method="post" style="display: inline;">
																					<input name="lawyer_email" type="hidden" value="'.$row['email'].'" />
																					<input name="lawyer_location" type="hidden" value="'.$row['city'].'" />
																					<input name="lawyer_category" type="hidden" value="'.$row['category'].'" />
																					<input type="submit" name="lawyer_submit" value="request" class="mb-2 btn btn-default btn-lg btn-block"/>
																				</form>
																			</div>
																		</div>
																	</div>
																	
									';
													}
												}
													?>
													<div class="form-group text-center">
							                         
							                          <label class="mr-3" for="searchcategory">Location</label>
							                          <select class="selectlocation">
							                          	<option disabled selected="">select</option>
							                          	<?php foreach ($select_lawyers_rslt as $datas) {
							                          if(preg_match("/{$mainkeyword}/i", $datas['category']) ||   preg_match("/{$mainkeyword}/i", $datas['category2']) || preg_match("/{$mainkeyword}/i", $datas['category3']) || preg_match("/{$mainkeyword}/i", $datas['category4'])  || preg_match("/{$mainkeyword}/i", $datas['category5'])  || preg_match("/{$mainkeyword}/i", $datas['category6'])) {?>


							                          	<option value="<?php echo $datas['address_line_1']?>" >
							                          		<?php echo $datas['address_line_1']?></option>
							                          		<?php }?>
							                          	<?php }?>
							                          </select>
							                        </div> 
								<?php 				}
												//} else{
												//	echo "No results";
												//}
											//}
										?>
										<br>
								</div>
						</div>
					</div>
				</div>

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
										<select class="form-control form-control-lg text-center" id="select location" name="case_location" required>
											<option value="">Select Location</option>
											<option value="karachi" <?php if(isset($_POST['lawyer_location'])){ if($_POST['lawyer_location']==="karachi"){ echo "selected"; } } ?> >Karachi</option>
											<option value="lahore" <?php if(isset($_POST['lawyer_location'])){ if($_POST['lawyer_location']==="lahore"){ echo "selected"; } } ?>>Lahore</option>
											<option value="islamabad" <?php if(isset($_POST['lawyer_location'])){ if($_POST['lawyer_location']==="islamabad"){ echo "selected"; } } ?>>Islamabad</option>
										</select>
									</div>
									<div class="form-group">
										<!-- <label>Case Category</label> -->
										<select name="case_category" class="form-control form-control-lg text-center" onchange="myFunction()" <?php if(isset($_POST['lawyer_category'])){ echo 'onload="myFunction()"'; } ?> id="case_category" required>
											<option value="">Select Case Category</option>
											<option value="Civil Law" <?php if(isset($_POST['lawyer_category'])){ if($_POST['lawyer_category']==="Civil Law"){ echo "selected"; } } ?> >Civil Law</option>
											<option value="Criminal Law" <?php if(isset($_POST['lawyer_category'])){ if($_POST['lawyer_category']==="Criminal Law"){ echo "selected"; } } ?> >Criminal Law</option>
											<option value="Corporate Law" <?php if(isset($_POST['lawyer_category'])){ if($_POST['lawyer_category']==="Corporate Law"){ echo "selected"; } } ?> >Corporate Law</option>
											<option value="Real Estate Law" <?php if(isset($_POST['lawyer_category'])){ if($_POST['lawyer_category']==="Real Estate Law"){ echo "selected"; } } ?> >Real Estate Law</option>
											<option value="Banking Law" <?php if(isset($_POST['lawyer_category'])){ if($_POST['lawyer_category']==="Banking Law"){ echo "selected"; } } ?> >Banking Law</option>
										</select>
									</div>
									<div class="form-group">
										<!-- <label>Case Sub-Category</label> -->
										<select name="case_subcategory" class="form-control form-control-lg text-center" id="case_subcategory" required>
											<option value="">Select Case Sub-Category</option>
										</select>
									</div>
									<div class="form-group">
										<!-- <label>Case Query</label> -->
										<select name="case_query" class="form-control form-control-lg text-center" id="case_query" required>
											<option value="">Select Case Query</option>
										</select>
									</div>
									<div class="form-group mt-2">
										<!-- <label for="">Case Details</label> -->
										<textarea name="case_detail" class="form-control form-control-lg" rows="5" placeholder="More Details" required></textarea>
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
                    } elseif($_SESSION['case_query'] == "Custody Matter"){
                      $price_range = "20000 - 50000";
                    } elseif($_SESSION['case_query'] == "Maintenance"){
                      $price_range = "20000 - 50000";
                    } elseif($_SESSION['case_query'] == "Guardianship"){
                      $price_range = "20000 - 50000";
                    }

                    if($_SESSION['case_query'] == "Defamation Damage"){
                      $price_range = "35000 - 60000";
                    } elseif($_SESSION['case_query'] == "Property Damage"){
                      $price_range = "35000 - 70000";
                    } elseif($_SESSION['case_query'] == "Compenstory"){
                      $price_range = "45000 - 90000";
                    }

                    if($_SESSION['case_subcategory'] == "Trial"){
                      if($_SESSION['case_query'] == "Murder"){
                        $price_range = "35000 - 90000";
                      } elseif($_SESSION['case_query'] == "Narcotics/Drugs"){
                        $price_range = "40000 - 90000";
                      } elseif($_SESSION['case_query'] == "NAB"){
                        $price_range = "60000 - 110000";
                      } elseif($_SESSION['case_query'] == "Anticorruption"){
                        $price_range = "60000 - 120000";
                      } elseif($_SESSION['case_query'] == "Bounce Cheque"){
                        $price_range = "35000 - 60000";
                      } elseif($_SESSION['case_query'] == "Theft"){
                        $price_range = "35000 - 60000";
                      } elseif($_SESSION['case_query'] == "Fraud/Cheating"){
                        $price_range = "35000 - 60000";
                      } elseif($_SESSION['case_query'] == "Illegal Possesion"){
                        $price_range = "35000 - 60000";
                      }
                    } elseif($_SESSION['case_subcategory'] == "Bail"){
                      if($_SESSION['case_query'] == "Murder"){
                        $price_range = "25000 - 75000";
                      } elseif($_SESSION['case_query'] == "Narcotics/Drugs"){
                        $price_range = "25000 - 75000";
                      } elseif($_SESSION['case_query'] == "NAB"){
                        $price_range = "50000 - 100000";
                      } elseif($_SESSION['case_query'] == "Anticorruption"){
                        $price_range = "13000 - 15000";
                      } elseif($_SESSION['case_query'] == "Bounce Cheque"){
                        $price_range = "25000 - 50000";
                      } elseif($_SESSION['case_query'] == "Theft"){
                        $price_range = "25000 - 50000";
                      } elseif($_SESSION['case_query'] == "Kidnapping"){
                        $price_range = "25000 - 50000";
                      } elseif($_SESSION['case_query'] == "Fraud/Cheating"){
                        $price_range = "25000 - 50000";
                      }
                    }

                    if($_SESSION['case_query'] == "Company Registration"){
                      $price_range = "30000 - 50000";
                    } elseif($_SESSION['case_query'] == "Trademark"){
                      $price_range = "10000 - 25000";
                    } elseif($_SESSION['case_query'] == "Copyright"){
                      $price_range = "10000 - 25000";
                    } elseif($_SESSION['case_query'] == "Patent"){
                      $price_range = "10000 - 25000";
                    }

                    if($_SESSION['case_query'] == "Depositing Rent in Court"){
                      $price_range = "10000 - 30000";
                    } elseif($_SESSION['case_query'] == "Rent Case Trial"){
                      $price_range = "20000 - 50000";
                    } elseif($_SESSION['case_query'] == "Stoppage of Ammenities"){
                      $price_range = "10000 - 30000";
                    }

                    // price for case category criminal law
                    if($_SESSION['case_query'] == "Special Court Offence in Bank"){
                      $price_range = "30000 - 60000";
                    } elseif($_SESSION['case_query'] == "Load Recovery"){
                      $price_range = "50000 - 80000";
                    } elseif($_SESSION['case_query'] == "Criminal Complaint"){
                      $price_range = "30000 - 60000";
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
                                <input name="case_price" type="hidden" value="<?php echo $price_range; ?>" />
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
			 //$('#searchselectmanual').select2();
		$(document).ready(function(){
			$(".selectlocation").on("change", function(){
				$('.containeroflaywer').css('background-color', 'unset');
				let selection=$(".selectlocation").val();
				$('.containeroflaywer:contains("'+selection+'")').css('background-color', '#27da98');

			})
			$("#case_subcategory").hide();
			$("#case_query").hide();

			$("#case_category").on("change", function(){

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
						$("#case_subcategory").empty().append("<option value=''> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}

					$("#case_subcategory").show();
				} else if(cc_sel_val === "Criminal Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Bail'> Bail </option>";
					var csc_opt2 = "<option value='Trial'> Trial </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option value=''> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}

					$("#case_subcategory").show();
				} else if(cc_sel_val === "Corporate Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Company Law'> Company Law </option>";
					var csc_opt2 = "<option value='IPO'> IPO </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option value=''> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2);
					}

					$("#case_subcategory").show();
				} else if(cc_sel_val === "Real Estate Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Rent'> Rent </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option value=''> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1);
					}

					$("#case_subcategory").show();
				} else if(cc_sel_val === "Banking Law"){
					//case sub-category options for categories 
					var csc_opt1 = "<option value='Bank Offence'> Bank Offence </option>";
					var csc_opt2 = "<option value='Recovery'> Recovery </option>";
					var csc_opt3 = "<option value='Criminal Offence'> Criminal Offence </option>";

					if($("#case_subcategory option").length <= 1){
						$("#case_subcategory").append(csc_opt1, csc_opt2, csc_opt3);
					} 

					if($("#case_subcategory option").length >= 1){
						$("#case_subcategory").empty().append("<option value=''> Select Case Sub-Category </option>");
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_subcategory").append(csc_opt1, csc_opt2, csc_opt3);
					}

					$("#case_subcategory").show();
				} else if(cc_sel_val === ""){
					$("#case_subcategory").hide();
					$("#case_query").hide();
				}
			});

			$("#case_subcategory").on("change", function(){

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
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Damages"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Defamation Damage'> Defamation Damage </option>";
					var cq_opt2 = "<option value='Property Damage'> Property Damage </option>";
					var cq_opt3 = "<option value='Compensatory'> Compensatory </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Bail"){
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
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Trial"){
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
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3, cq_opt4, cq_opt5, cq_opt6, cq_opt7, cq_opt8);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Company Law"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Company Registration'> Company Registration </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "IPO"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Trademark'> Trademark </option>";
					var cq_opt2 = "<option value='Copy Right'> Copy Right </option>";
					var cq_opt3 = "<option value='Patent'> Patent </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Rent"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Depositing Rent in Court'> Depositing Rent in Court </option>";
					var cq_opt2 = "<option value='Rent Case Trial'> Rent Case Trial </option>";
					var cq_opt3 = "<option value='Stoppage of Ammenities'> Stoppage of Ammenities </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1, cq_opt2, cq_opt3);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Bank Offence"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Special Court Offence in Bank'> Special Court Offence in Bank </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Recovery"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Loan Recovery'> Loan Recovery </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === "Criminal Offence"){
					//case sub-category options for categories 
					var cq_opt1 = "<option value='Criminal Complaint'> Criminal Complaint </option>";

					if($("#case_query option").length <= 1){
						$("#case_query").append(cq_opt1);
					} 

					if($("#case_query option").length >= 1){
						$("#case_query").empty().append("<option value=''> Select Case Query </option>");
						$("#case_query").append(cq_opt1);
					}

					$("#case_query").show();
				}	else if(csc_sel_val === ""){
					$("#case_query").hide();
				}
			});
		});
		</script>

		<script>
			$(document).ready(function(){
				$('input:radio[name="searchoption"]').on("change", function(){
					var search_option = $(this).val();
					if(search_option === "name"){
						$("#searchstr").show();
						$("#searchselect").hide();
					} else if(search_option === "email"){
						$("#searchstr").show();
						$("#searchselect").hide();
					} else if(search_option === "category"){

						$("#searchstr").hide();
						$("#searchselect").show();
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

			if(isset($search_modal)) {
				if($search_modal === true) {
					?>
						<script> 
							$('#searchresultmodal').modal('show');
						</script>
					<?php
				}
			}

			if(isset($show_casemodal)) {
				if($show_casemodal === true) {
					?>
						<script> 
							$('#casemodal').modal('show');
						</script>
					<?php
				}
			}
		?>
<style type="text/css">
	/* Main Classes */
.myinput[type="checkbox"]:before {
  position: relative;
  display: block;
  width: 11px;
  height: 11px;
  border: 1px solid #808080;
  content: "";
  background: #FFF;
}

.myinput[type="checkbox"]:after {
  position: relative;
  display: block;
  left: 2px;
  top: -11px;
  width: 7px;
  height: 7px;
  border-width: 1px;
  border-style: solid;
  border-color: #B3B3B3 #dcddde #dcddde #B3B3B3;
  content: "";
  background-image: linear-gradient(135deg, #B1B6BE 0%, #FFF 100%);
  background-repeat: no-repeat;
  background-position: center;
}

.myinput[type="checkbox"]:checked:after {
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAQAAABuW59YAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAB2SURBVHjaAGkAlv8A3QDyAP0A/QD+Dam3W+kCAAD8APYAAgTVZaZCGwwA5wr0AvcA+Dh+7UX/x24AqK3Wg/8nt6w4/5q71wAAVP9g/7rTXf9n/+9N+AAAtpJa/zf/S//DhP8H/wAA4gzWj2P4lsf0JP0A/wADAHB0Ngka6UmKAAAAAElFTkSuQmCC'), linear-gradient(135deg, #B1B6BE 0%, #FFF 100%);
}

.myinput[type="checkbox"]:disabled:after {
  -webkit-filter: opacity(0.4);
}
</style>
</body>

</html>
<?php
	} else{
			header('location: log-out.php');
	}
?>