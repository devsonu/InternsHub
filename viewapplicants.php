<?php
	// Page for the employer to view all the applications on their internship.

	session_start();

	require_once('./inc/checkinstall.php');
	require_once('./inc/config.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>
		<?php
		    echo $config['appname'];
		?> - Inernship Applications 
	</title>

	<?php include './fragments/head.html'; ?>
</head>
<body>
	<main class="container-fluid">
		<?php include './fragments/header.php'; ?>

		<div class="fixedContainer dashboard">
			<?php
				// Checking if the user is logged in and is an employer.

				if(!$_SESSION['int_loggedin'] || !$_SESSION['int_userid']){
					// User is not logged in and highly unauthorised to visit this page.

					header("refresh:0;url=./login.php");	// Redirect immediately to the login page.
					exit();
				}

				// Now checking if the user is an employer or not, and also getting all the details about them.

				$user = mysqli_query($db, "SELECT * FROM internshub_users WHERE userid = '".$_SESSION['int_userid']."'");

				$user = mysqli_fetch_assoc($user);

				if(!$user['isemployer']){
					// The user is not authorised to visit this page.

					?>
						<div class="alert alert-danger">
							You are unauthorised to view this page.
						</div>
					<?php

					header("refresh:1.5;url=./user.php");		// Redirect the user to the dashboard.
					exit();
				}

				// If the user is an employer, then render the list of applications on their internships.
				// Pagination included.

				$allApplications = mysqli_query($db,
					"SELECT * FROM internshub_applications WHERE employerid='".$_SESSION['int_userid']."';"
				);

				$totalApps = mysqli_num_rows($allApplications);		// Total number of applications in the database.

				$appsperpage = 10;		// The number of applications to display on one page.

				// Check if a page no has been passed to the page.

				if($_GET['page'])
					$pageno = $_GET['page'];

				if($pageno <= 0 || ($pageno-1)*$appsperpage >= $totalApps){	// Invalid page number.
					$pageno = 1;
				}

				// Requesting the database for the applications on the current page.

				$pageApplications = mysqli_query($db, 
					"SELECT * FROM internshub_applications 
						WHERE employerid ='".$_SESSION['int_userid']."'
						ORDER BY created DESC LIMIT ".$appsperpage." OFFSET "
						.($pageno-1)*$appsperpage.";"
				);

				if(mysqli_num_rows($pageApplications) <= 0){
					// No applicants found.
				?>
					<div align="center" style="margin: 9.75rem 0;">
						<!-- Position this at almost the center of the screen. -->
						<div class="roundedIcon">
							<i class="fas fa-users fa-3x"></i>
						</div>
						<br/>
						<br/>
						No Applicants found.
					</div>
				<?php

				}

				$prev = false;
				$next = false;

				// Rendering each application one by one.

				while($application = mysqli_fetch_assoc($pageApplications)){

					// Getting the details of applicant and the internship.

					$internship = mysqli_query($db,
						"SELECT * FROM internshub_internships WHERE intid = '".$application['intid']."';"
					);

					if($internship){
						$internship = mysqli_fetch_assoc($internship);
					}
					else{
						?>
							<div class="alert alert-danger">
								An error occured. Kindly try again.
							</div>
						<?php

						exit();		// Stop execution here itself.
					}

					// Now getting the details of the user.

					$user = mysqli_query($db,
						"SELECT * FROM internshub_users WHERE userid = '".$application['applicantid']."';"
					);

					if($user){
						$user = mysqli_fetch_assoc($user);
					}
					else{
						?>
							<div class="alert alert-danger">
								An error occured. Kindly try again.
							</div>
						<?php

						exit();		// Stop execution here itself.
					}

					echo "
						<div class='application'>
							<span>
								For : <a href='./internship.php?intid=".$internship['intid']."'>
										<span class='int-title'>".$internship['title']."</span>
									  </a>
							</span>
							
							<br/>

							<span>
								<span class='int-username'>".$user['name']."</span>
							</span>

							<br/>
							
							<span class='created'>
								".$application['created']."
							</span>

							<a href='./application.php?appid=".$application['appid']."'>
								<button class='btn btn-info'>
									View All Details
								</button>
							</a>
						</div><br/>
					";
				}
			?>

			<?php

				// Now checking if there is a further page to display.

				if($pageno*$appsperpage > $appsperpage && $totalApps > $appsperpage)
                    $prev = true;

                if($pageno*$appsperpage < $totalApps)
                    $next = true;

                // Displaying the pagination buttons.

                ?>

                <div align="center">
	                <ul class="pager">
	                	<?php
	                		if($prev){
	                			?>
	                				<li class='previous'>
	                					<a href="./viewapplicants.php?page=<?php echo $pageno - 1; ?>">
	                						<i class="fas fa-arrow-circle-left fa-lg"></i>
	                					</a>
	                				</li>
	                			<?php
	                		}

	                		if($next){
	                			?>
	                				<li class="next">
	                					<a href="./viewapplicants.php?page=<?php echo $pageno + 1; ?>">
	                						<i class="fas fa-arrow-circle-right fa-lg"></i>
	                					</a>
	                				</li>
	                			<?php
	                		}
	                	?>
	                </ul>
	                <br/>
	            </div>
		    <?php
		    ?>

			<div align="center">
				<div class="bottomLine"></div>
			</div>
		</div>

		<?php include './fragments/footer.html'; ?>
	</main>
</body>
</html>