<?php

// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();

//Initialize the session
session_start();

// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$email = $password = $studentID = $firstName = $lastName = $major = "";

//Professor Sign up variables
$emailP = $passwordP = $firstNameP = $lastNameP = $majorP = "";
$profID = $isAdmin = "";

//Variables for studentLogin
$stdemail = $stdpassword = "";
$stdemail_err = $stdpassword_err = "";

//Variables for studentLogin
$emailProfLog = $passwordProfLog = "";
$emailProfLog_err = $passwordProfLog_err = "";

// Processing form data when form is submitted
if ($_POST['student_signup']){

	//Validate studentID
    if(empty(trim($_POST["stid"])))
    {
		$stid_err = "Please enter a studentID";
	}
    else if(strlen(trim($_POST["stid"])) != 9)
    {
		$stid_err = "Student number must be 9 numbers long.";
	}
    else
    {
		$sql = "SELECT * FROM student WHERE Student_ID = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_studentID);
            
            $param_studentID = trim($_POST["stid"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	$stid_err = "This student ID is already in use.";
                }
                else
                {
                	$studentID = trim($_POST["stid"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    
    //Validate First Name
    if(empty(trim($_POST["fname"]))){
        $fname_err = "Please enter a first name.";     
    }
    else
    {
        $firstName = trim($_POST["fname"]);
    }
    //Last name
    if(empty(trim($_POST["lname"]))){
        $lname_err = "Please enter a last name.";     
    }
    else
    {
        $lastName = trim($_POST["lname"]);
    }
    
    //Email 
    if(empty(trim($_POST["emailS"]))){
        $email_err = "Please enter an email.";     
    }
 	else
      {
          $sql = "SELECT * FROM student WHERE Email = ?";

          if($stmt = mysqli_prepare($link, $sql))
          {
              mysqli_stmt_bind_param($stmt, "s", $param_email);

              $param_email = trim($_POST["emailS"]);

              if(mysqli_stmt_execute($stmt))
              {
                  mysqli_stmt_store_result($stmt);

                  if(mysqli_stmt_num_rows($stmt) == 1)
                  {
                      $stid_err = "This email is already in use.";
                  }
                  else
                  {
                      $email = trim($_POST["emailS"]);
                  }
              }
              else
              {
                  echo "Oops! Something on our end went wrong! Try again later!";
              }
              mysqli_stmt_close($stmt);
          }
      }
      
    //Major 
    if(empty(trim($_POST["smajor"]))){
        $major_err = "Please enter a major.";     
    }
    else
    {
        $major = trim($_POST["smajor"]);
    }
    //Password 
    if(empty(trim($_POST["PasswordS"]))){
        $password_err = "Please enter a password.";     
    }
    else
    {
        $password = trim($_POST["PasswordS"]);
    }    
	
    //Check erros then we gucci to put it into the database
    if(empty($stid_err) && empty($fname_err) && empty($lname_err) && empty($email_err) && empty($major_err) && empty($password_err)) 
    {
    	//Prepare the insert statement
        $sql = "INSERT INTO student (Student_ID, First_Name, Last_Name, Email, Password, Major) VALUES (?, ?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "ssssss", $param_studentID, $param_fname, $param_lname, $param_email, $param_password, $param_major);

            //set parameter variables
            $param_studentID = $studentID;
            $param_fname = $firstName;
            $param_lname = $lastName;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);;
            $param_major = $major;
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                //Do we want to add like a "you've signed up successfully!"
            } else{
                echo "Something went wrong. Please try again later.";
            }

			//Close statement
            mysqli_stmt_close($stmt);
        }
    }
    //close connection 
    mysqli_close($link);
    
 }
else if ($_POST['professor_signup']){

	//Validate studentID
    if(empty(trim($_POST["emailP"])))
    {
		$emailP_err = "Please enter an email.";
	}
    else
    {
		$sql = "SELECT * FROM professor WHERE Email = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            $param_email = trim($_POST["emailP"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	$emailP_err = "This email is already in use by another professor.";
                }
                else
                {
                	$emailP = trim($_POST["emailP"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
         }
	}
    
    //Validate First Name
    if(empty(trim($_POST["fnameP"]))){
        $fnameP_err = "Please enter a first name.";     
    }
    else
    {
        $firstNameP = trim($_POST["fnameP"]);
    }
    //Last name
    if(empty(trim($_POST["lnameP"]))){
        $lnameP_err = "Please enter a last name.";     
    }
    else
    {
        $lastNameP = trim($_POST["lnameP"]);
    }
   
    //Major 
    if(empty(trim($_POST["majorP"]))){
        $majorP_err = "Please enter a major.";     
    }
    else
    {
        $majorP = trim($_POST["majorP"]);
    }
    //Password 
    if(empty(trim($_POST["PasswordP"]))){
        $passwordP_err = "Please enter a password.";     
    }
    else
    {
        $passwordP = trim($_POST["PasswordP"]);
    }    
    
    //Check erros then we gucci to put it into the database
    if(empty($fnameP_err) && empty($lnameP_err) && empty($emailP_err) && empty($majorP_err) && empty($passwordP_err)) 
    {
    	//Prepare the insert statement
        $sql = "INSERT INTO professor (First_Name, Last_Name, Email, Password, Is_Admin, Major) VALUES (?, ?, ?, ?, 0, ?)";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "sssss", $param_fnameP, $param_lnameP, $param_emailP, $param_passwordP, $param_majorP);

            //set parameter variables
            $param_fnameP = $firstNameP;
            $param_lnameP = $lastNameP;
            $param_emailP = $emailP;
            $param_passwordP = password_hash($passwordP, PASSWORD_DEFAULT);;
            $param_majorP = $majorP;
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                //Do we want to add like a "you've signed up successfully!"
            } else{
                echo "Something went wrong. Please try again later.";
            }

			//Close statement
            mysqli_stmt_close($stmt);
        }
    }
    //close connection
    mysqli_close($link);
    
}
else if ($_POST['student_login'])
{
	// Check if username is empty
    if(empty(trim($_POST["stdemail"]))){
        $stdemail_err = "Please enter an email.";
    } else{
        $stdemail = trim($_POST["stdemail"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["stdpassword"]))){
       $stdpassword_err = "Please enter your password.";
    } else{
        $stdpassword = trim($_POST["stdpassword"]);
    }

	//Validate email and password
    if(empty($stdemail_err) && empty($stdpassword_err))
    {
   		$sql = "SELECT Student_ID, First_Name, Last_Name, Email, Password, Major FROM student WHERE Email = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_email);

         	$param_email = $stdemail;

            //Attempt to execute
            if(mysqli_stmt_execute($stmt))
            {
            	//Store result
                mysqli_stmt_store_result($stmt);
                
                //Check if the email already exists, if yes the verify password
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	//bind result to variables
                    mysqli_stmt_bind_result($stmt, $studentID, $firstName, $lastName, $stdemail, $hashed_password, $major);
                    
                    if(mysqli_stmt_fetch($stmt))
                    {
                    	if(password_verify($stdpassword, $hashed_password))
                        {
                            //password is gucci, so let's start a new session
                            session_start();
                            
                            //Store data in session variables
                            $_SESSION["studentLoggedin"] = true;
                            $_SESSION["studentID"] = $studentID;
                            $_SESSION["firstName"] = $firstName;
                            $_SESSION["lastName"] = $lastName;
                            $_SESSION["email"] = $stdemail;   
                            $_SESSION["major"] = $major;
                            
                            // Redirect user to welcome page
                            header("location: studentAccount.php");
                        }
                        else
                        {
                        	//Display incorrect password msg
                            $stdpassword_err = "The password you entered was not valid.";
                        }
                    }
                }
                else
                {
                	//Incorrect email
                    $stdemail_err = "No account found with that email.";
                }
            }
            else
            {
            	echo "Oops! Something went wrong. Please try again later!";
            }
            //Close statement
            mysqli_stmt_close($stmt);
        
        }
    }
    
    //Close connection
    mysqli_close($link);
    
    
}
else if ($_POST['professor_login'])
{
	// Check if email is empty
    if(empty(trim($_POST["emailProfLog"]))){
        $emailProfLog_err = "Please enter an email.";
    } else{
        $emailProfLog = trim($_POST["emailProfLog"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["passwordProfLog"]))){
       $passwordProfLog_err = "Please enter your password.";
    } else{
        $passwordProfLog = trim($_POST["passwordProfLog"]);
    }

	//Validate email and password
    if(empty($emailProfLog_err) && empty($passwordProfLog_err))
    {
   		$sql = "SELECT Professor_ID, First_Name, Last_Name, Email, Password, Is_Admin, Major FROM professor WHERE Email = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_email);

         	$param_email = $emailProfLog;

            //Attempt to execute
            if(mysqli_stmt_execute($stmt))
            {
            	//Store result
                mysqli_stmt_store_result($stmt);
                
                //Check if the email already exists, if yes the verify password
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	//bind result to variables
                    mysqli_stmt_bind_result($stmt, $profID, $firstNameP, $lastNameP, $emailProfLog, $hashed_password, $isAdmin, $majorP);
                    
                    if(mysqli_stmt_fetch($stmt))
                    {
                    	if(password_verify($passwordProfLog, $hashed_password))
                        {
                            //password is gucci, so let's start a new session
                            session_start();
                            
                            //Store data in session variables
                            $_SESSION["professorLoggedin"] = true;
                            $_SESSION["profID"] = $profID;
                            $_SESSION["firstNameP"] = $firstNameP;
                            $_SESSION["lastNameP"] = $lastNameP;
                            $_SESSION["emailP"] = $emailProfLog;   
                            $_SESSION["majorP"] = $majorP;
                            $_SESSION["isAdmin"] = $isAdmin;                            
                            
                            if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1){
                                // Redirect user to admin Page
                            	header("location: adminProfAccount.php");
                            }                            
                            else if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 0){
                                // Redirect user to regular prof page
                            	header("location: profAccount.php");
                            }
                        }
                        else
                        {
                        	//Display incorrect password msg
                            $passwordProfLog_err = "The password you entered was not valid.";
                        }
                    }
                }
                else
                {
                	//Incorrect email
                    $emailProfLog_err = "No account found with that email.";
                }
            }
            else
            {
            	echo "Oops! Something went wrong. Please try again later!";
            }
            //Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    //Close connection
    mysqli_close($link);
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>ULib - Online Textbook Library System</title>
        <link rel="stylesheet" href="styles/index.css">
    </head>
    <body>
        
        <div class="titleBox">
            <h1 id="header">ULIBS</h1>
            <h2 id="subtitle">Online Textbook Library System</h2>
        </div>
        <div id="navBox">
            <a href="#product">Product Information</a>
            <a href="#about">About Us</a>
            <a href="#contact">Contact Us</a>
            <a href="#" class="right" onclick="document.getElementById('signingUpStudent').style.display='block'">Student Sign Up</a>
            <a href="#" class="right" onclick="document.getElementById('signingUpProfessor').style.display='block'">Professor Sign Up</a>
        </div>
        
        <div class="photoGal">                 
            <div class="gallery">
			<a target="_blank" href="photos/admin_view.png">
                    <img src="photos/admin_view.png" alt="Admin" width="600" height="400">
                </a>
                <div class="description">Admin View</div>
            </div>

            <div class="gallery">
                <a target="_blank" href="photos/professor_view.png">
                    <img src="photos/professor_view.png" alt="professor" width="600" height="400">
                </a>
                <div class="description">Professor View</div>
            </div>

            <div class="gallery">
                <a target="_blank" href="photos/search_popup.png">
                    <img src="photos/search_popup.png" alt="search" width="600" height="400">
                </a>
                <div class="description">Search Popup</div>
            </div>

            <div class="gallery">
                <a target="_blank" href="photos/professor_view2.png">
                    <img src="photos/professor_view2.png" alt="professor" width="600" height="400">
                </a>
                <div class="description">Another Professor View</div>
            </div>

            <div class="gallery">
                 <a target="_blank" href="photos/student_view.png">
                    <img src="photos/student_view.png" alt="student" width="600" height="400">
                </a>
                <div class="description">Student View</div>
            </div>

            <div class="gallery">
                <a target="_blank" href="photos/edit_profile.png">
                    <img src="photos/edit_profile.png" alt="profile" width="600" height="400">
                </a>
                <div class="description">Edit Profile Popup</div>
            </div>
        </div>

        <div class="mainContent">
            <div class="side">
                <div class="login">
                    <h2 class="subtitle">Students Login Here!</h2>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    
                          <label for="stdemail">Email Address: </label>
                          <input type="text" id="stdemail" name="stdemail" placeholder="example@email.ca"></input>
                        
                          <label for="stdpassword">Password: </label>
                          <input type="text" id="stdpassword" name="stdpassword" placeholder="password"></input>
                        
                        <input type="submit" name="student_login"  value="Login">
                    </form> 
                </div>
                <hr>
                <div class="login">
                    <h2 class="subtitle">Professors Login Here!</h2>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <label for="email">Email Address: </label>
                        <input type="text" id="emailProfLog" name="emailProfLog" placeholder="example@email.ca"></input>
                        
                        <label for="password">Password: </label>
                        <input type="text" id="passwordProfLog" name="passwordProfLog" placeholder="password"></input>
                        <input type="submit" name="professor_login" value="Login">
                    </form>
                
                </div>

            </div>
            
            <div class="main">
                <div class="productInfo" id="product">
                    <h2 class="subtitle">Product Information</h2>
                    <p>&emsp;&emsp;&emsp;Welcome to ULIBS! This is a personal University-style library management system for professors to manage their books. Other features include being able to see who has them and for what courses they could relate to. Similarly, it allows for larger libraries to manage larger library systems, and help students organize what they borrow. This would allow students to go to libraries and offices to retrieve a book, login, and then list it as borrowed with the professor and/or librarian. With the ability to assign books to courses, many professors are also able to assign required materials and request that a given library can list it for their course. Similarly, they can also choose to add the book to his/her personal library system. 
                        <br/>&emsp;&emsp;&emsp;Student accounts can borrow books and search amongst what is available in the database. Professors can create libraries, add or remove books, and manage personal information. Admins can do all of this and more! 
                        <br/>&emsp;&emsp;&emsp;The system itself is managed by database administrators as well as professors that are admins. Only other admins or database admins can assign or remove admin priveledges. So if one wishes to request admin access, you can visit the contact us section of the home page or contact other admins! 
                        <br/>&emsp;&emsp;&emsp;Overall, this application will allow for easy access and management of books, textbooks, and library systems.
                                    </p>
                </div>

                <div class="aboutUs" id="about">
                    <h2 class="subtitle">About Us</h2>
                    <p>Let's meet the team! 
                    
                        <br/><br/>Our team name is "DROP TABLE TEAM_NAME;" and we consist of two developers! 
                        <br/><br/>Bryce Hughson is in charge of all things backend and server within this application. He is in charge of managing and connecting the application to the database and servers.
                        <br/><br/>Andrea Bonato is in charge of all things front end within this application. She is in charge of creating all the visuals and content on all the webpages.
                    
                        <br/><br/>We are happy to meet you!!</p>
                </div>

                <div class="contactUs" id="contact">
                    <h2 class="subtitle">Contact Us</h2>
                    <p>Questions? Comments? Concerns? Want to be an admin? 
                    
                       <br/><br/>Contact us! </p>
                    <form action="mailto:bonato11@uwindsor.ca" method="POST" enctype="text/plain" class="emailContentBox">
                        <label for="emailC">Email Address: </label>
                        <input type="text" id="emailC" name="emailC" placeholder="example@email.ca"></input>
                        <label for="name">Name: </label>
                        <input type="text" id="name" name="name" placeholder="John Doe"></input>
                        
                        <label for="emailContent">Email Content: </label>
                        <textarea class="emailBox" type="text" id="emailContent" name="emailContent" placeholder="Enter email content..."></textarea>
                        <input type="submit" value="Send">
                    </form>
                
                </div>
            </div>
        </div>

        <div id="signingUpStudent" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form">
                <div class="signContainer">
                    <h1> Student Sign Up!</h1>
                    
                    <div class="form-group <?php echo (!empty($stid_err)) ? 'has-error' : ''; ?>" >
                        <label for="stid">Student ID: </label> 
                        <input type="text" id="stid" name="stid" placeholder="123456789" value="<?php echo $studentID; ?>"></input>
                        <span class="help-block" style="color:red;font-size:small;"><?php echo $stid_err; ?></span><br/>
                     </div>
                    
                    <div class="form-group <?php echo (!empty($fname_err)) ? 'has-error' : ''; ?>" >
                      <label for="fname">First Name </label> 
                      <input type="text" id="fname" name="fname" placeholder="John "value="<?php echo $firstName; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $fname_err; ?><br></span>
                    </div>

					<div class="form-group <?php echo (!empty($lname_err)) ? 'has-error' : ''; ?>" >
                      <label for="lname">Last Name: </label> 
                      <input type="text" id="lname" name="lname" placeholder="Doe" value="<?php echo $lastName; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $lname_err; ?><br></span>
                    </div>
                     
                    <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>" >
                      <label for="emailS">Email Address: </label> 
                      <input type="text" id="emailS" name="emailS" placeholder="example@gmail.ca" value="<?php echo $email; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $email_err; ?><br></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>" >
                      <label for="PasswordS">Password: </label> 
                      <input type="text" id="PasswordS" name="PasswordS" placeholder="password" value="<?php echo $password; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $password_err; ?><br></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($major_err)) ? 'has-error' : ''; ?>">
                      <label for="smajor">Major: </label> 
                      <input type="text" id="smajor" name="smajor" placeholder="Computer Science" value="<?php echo $major; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $major_err; ?><br></span>
                    </div>                    
                    
                    <input type="submit" name="student_signup" value="Sign Up" onclick="showPopUp1()">
                    <input type="button" value="Exit" onclick="document.getElementById('signingUpStudent').style.display = 'none'">
                
                </div>
            </form>
        </div>

        <div id="signingUpProfessor" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Professor Sign Up!</h1>
                    
                    <div class="form-group <?php echo (!empty($fnameP_err)) ? 'has-error' : ''; ?>">
                    	<label for="fnameP">First Name </label> 
                    	<input type="text" id="fname" name="fnameP" placeholder="John"></input>
						<span class="help-block" style="color:red;font-size:small;"><?php echo $fnameP_err; ?><br></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($lnameP_err)) ? 'has-error' : ''; ?>">
                   	 	<label for="lnameP">Last Name: </label>   
                    	<input type="text" id="lname" name="lnameP" placeholder="Doe"></input>
                     	<span class="help-block" style="color:red;font-size:small;"><?php echo $lnameP_err; ?><br></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($emailP_err)) ? 'has-error' : ''; ?>">
                    <!--Only other admins can assign admins. So they can't enter isAdmin-->
                      <label for="emailP">Email Address: </label> 
                      <input type="text" id="emailS" name="emailP" placeholder="example@gmail.ca"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $emailP_err; ?><br></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($passwordP_err)) ? 'has-error' : ''; ?>">
                      <label for="PasswordP">Password: </label> 
                      <input type="text" id="PasswordS" name="PasswordP" placeholder="password"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $passwordP_err; ?><br></span>
                    </div>
					
                    <div class="form-group <?php echo (!empty($majorP_err)) ? 'has-error' : ''; ?>">
                      <label for="Pmajor">Major: </label> 
                      <input type="text" id="smajor" name="majorP" placeholder="Computer Science"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $majorP_err; ?><br></span>
                    </div>  
                    
                    <input type="submit" name="professor_signup" value="Sign Up" onclick="showPopUp2()">
                    <input type="button" value="Exit" onclick="document.getElementById('signingUpProfessor').style.display = 'none'">
                
                </div>
            </form>
        </div>


        <script>
            var popUp1 = document.getElementById('signingUpStudent');
            var navbar = document.getElementById('navBox');
            var sticky = navbar.offsetTop;
            var popUp2 = document.getElementById('signingUpProfessor');
            
           function showPopUp1() {
           		popUp1.display = "block";
                localStorage.setItem('popUp1Show', 'true');
           }
           function showPopUp2() {
           		popUp2.display = "block";
                localStorage.setItem('popUp2Show', 'true');
           }
           
           function load() {
           		var popUp1Show = localStorage.getItem('popUp1Show');
                var popUp2Show = localStorage.getItem('popUp2Show');
                if(popUp1Show === 'true') {
                	popUp1.style.display = "block";
                    localStorage.removeItem('popUp1Show');
                  
                } 
                if(popUp2Show === 'true') {
                	popUp2.style.display = "block";
                    localStorage.removeItem('popUp2Show');
                  
                } 
                
           }
           
           window.onload = load;
           
           window.onclick = function(event) {
                if (event.target == popUp1) {
                    popUp1.style.display = "none";
                    localStorage.removeItem('popUp1Show');
                } 
                if (event.target == popUp2) {
                    popUp2.style.display = "none";
                    
                }
            }

            window.onscroll = function() {
                if (window.pageYOffset >= sticky) {
                    navbar.classList.add("sticky");
                } else {
                    navbar.classList.remove("sticky");
                }
            }
          
        </script>
    </body>
</html>   