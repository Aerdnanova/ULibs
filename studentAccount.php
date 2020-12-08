<?php
//Init session
session_start();

//Check if prof is logged in 
if(!isset($_SESSION["studentLoggedin"]) && $_SESSION["studentLoggedin"] === false)
{
	header("index.php");
    exit;
}

// Include config file
require_once "config.php";

//Edit Profile
$fname = $lname = $email = $password = $major = "";
$fname_err = $lname_err = $email_err = $password_err = $major_err = "";

//Borrow Book
$bookID_err = "";
$bookID = "";

$borrowDate = date("Y-m-d");

//return Book
$rbookID = "";

if ($_POST['editProfile']){

	//Validate cid
    if(empty(trim($_POST["fname"])))
    {
		$fname = $_SESSION["firstNameP"];
	}
    else
    {
		$fname = trim($_POST["fname"]);
	}
    
    if(empty(trim($_POST["lname"])))
		$lname= $_SESSION["lastNameP"];
    else
		$lname = trim($_POST["lname"]);
    
    if(empty(trim($_POST["email"])))
		$email = $_SESSION["emailP"];
    else
		$email = trim($_POST["email"]);
        
    if(empty(trim($_POST["major"])))
		$major = $_SESSION["majorP"];
    else
		$major = trim($_POST["major"]);
        
        $sql = "UPDATE student SET First_Name = '{$fname}', Last_Name = '{$lname}', Email = '{$email}', Major ='{$major}'  WHERE Student_ID = '{$_SESSION['studentID']}'";
        
        //Update Session variables accordingly
        $_SESSION["firstName"] = $fname;
        $_SESSION["lastName"] = $lname;
        $_SESSION["email"] = $email;
        $_SESSION["major"] = $major;        
        
        mysqli_query($link, $sql);
        
     header('Location: studentAccount.php');
    //close connection
    mysqli_close($link);
    
}
else if ($_POST["borrowBook"])
{
	//Validate studentID
    if(empty(trim($_POST["bookID"])))
    {
		$bookID_err = "Please enter a Book ID.";
	}
    else if (!empty(trim($_POST["bookID"])))
    {
		$sql = "SELECT * FROM book WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_bookID);
            
            $param_bookID = trim($_POST["bookID"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0)
                {
                	$bookID_err = "This book ISBN doesn't exist";
                }
                else
                {
                	$bookID = trim($_POST["bookID"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    else if($NumAva == 0)
    {
 			$bookID_err = "This book is out of stock";
	}
    
        //We need to get title and num available variables
        $sql = "SELECT Title, No_Available FROM search WHERE ISBN = '{$bookID}'";

        if($result = mysqli_query($link, $sql))
        {
        	if(mysqli_num_rows($result) > 0)
            {
            	while($row = mysqli_fetch_array($result))
                {
                	$Title = $row['Title'];
                    $NumAva = $row['No_Available'];
                }
                // Free result set
                mysqli_free_result($result);
          	}
        } 
        else
        {
        	echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
       	}
    
    if(empty($bookID_err))
    {        
        
        $gracePeriod = strtotime('+1 Week');
        $dueDate = date("Y-m-d", $gracePeriod);
        
        $sql = "INSERT INTO borrows (Student_ID, ISBN, Borrow_Date, Due_Date) VALUES ('{$_SESSION['studentID']}', '{$bookID}', '{$borrowDate}',  '{$dueDate}')";
        mysqli_query($link, $sql);
        
        //Update Belongs to
        $sqlBT = "UPDATE belongs_to SET No_Available = ('{$NumAva}' - 1) WHERE Title = '{$Title}'";
        mysqli_query($link, $sqlBT);
        
        //Update search
        $sqlSearch = "UPDATE search SET No_Available = ('{$NumAva}' - 1) WHERE ISBN = '{$bookID}'";
        mysqli_query($link, $sqlSearch); 
        
        header('Location: studentAccount.php');
    }
    
    
   	//close connection
    mysqli_close($link);
    
}
else if ($_POST["searchBar"])
{
	$_SESSION["searchBar"] = trim($_POST["search"]);
    
    header('Location: studentAccount.php');
}
else if ($_POST["returnBook"])
{    
		$rbookID = $_POST["rbookID"];

        //We need to get title and num available variables
        $sql = "SELECT Title, No_Available FROM search WHERE ISBN = '{$rbookID}'";

        if($result = mysqli_query($link, $sql))
        {
        	if(mysqli_num_rows($result) > 0)
            {
            	while($row = mysqli_fetch_array($result))
                {
                	$Title = $row['Title'];
                    $NumAva = $row['No_Available'];
                }
                // Free result set
                mysqli_free_result($result);
          	}
        } 
        else
        {
        	echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
       	}

        //Remove from borrows
        $sql = "DELETE FROM borrows WHERE Student_ID = '{$_SESSION["studentID"]}' AND ISBN = '{$rbookID}'";
        mysqli_query($link, $sql);
        
        //Update Belongs to
        $sqlBT = "UPDATE belongs_to SET No_Available = ('{$NumAva}' + 1) WHERE Title = '{$Title}'";
        mysqli_query($link, $sqlBT);
        
        //Update search
        $sqlSearch = "UPDATE search SET No_Available = ('{$NumAva}' + 1) WHERE ISBN = '{$rbookID}'";
        mysqli_query($link, $sqlSearch); 
        
        header('Location: studentAccount.php');
    
   	//close connection
    mysqli_close($link);
    
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>ULib - Online Textbook Library System</title>
        <link rel="stylesheet" href="styles/profAccount.css">
    </head>
    <body>
        
        <div class="titleBox">
            <h1 id="header">ULibs - Online Textbook Library System</h1>
        </div>

        <div id="navBox"> 
            <div class="dropdown"> 
                <button href="javascript:void(0)" class="dropbtn">Book Information</button>
                <div class="dropdown_content" id="dropdown">
                    <a href="#booksBorrowed">Borrowed Books</a>          
                    <a href="#lateBooks">Late Books</a>          
                    <a href="#" onclick="document.getElementById('borrowBook').style.display = 'block'">Borrow Book</a>
                    <a href="#" onclick="document.getElementById('returnBook').style.display = 'block'">Return Book</a>
                </div>
            </div>

            <a href="logout.php" class="right">Logout</a>
            <a href="#" class="right" onclick="document.getElementById('searchBook').style.display = 'block'">Search</a>
            <a href="#" class="right"  onclick="document.getElementById('editProfile').style.display = 'block'">Edit Profile</a>
        </div>
            
        <!--Pages that will be switched between making hidden and not hidden-->

        <div class="mainContent">
            <div class="profileInformation">
                <h2 class="welcome">Welcome back <?php echo $_SESSION["firstName"] . " " . $_SESSION["lastName"];?>!</h2>
            </div>
            
            <div id="booksBorrowed">
                <!--Loaded for the libraries that this specific owns-->
                <h2 class="subtitle">My Borrowed Books</h2>
                <table id="libraries">
                    <tr>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Description</th>
                        <th>Due Date</th>
                    </tr>
					<?php 
                              include 'config.php';

                              $sql = " SELECT * FROM book INNER JOIN borrows ON book.ISBN = borrows.ISBN WHERE borrows.Student_ID = '{$_SESSION['studentID']}'";
                              if($result = mysqli_query($link, $sql))
                              {
                                  if(mysqli_num_rows($result) > 0)
                                  {
                                        while($row = mysqli_fetch_array($result))
                                        {
                                          echo '<tr>';
                                            echo '<td>' . $row['ISBN'] . '</td>';
                                            echo '<td>' . $row['Title'] . '</td>';
                                            echo '<td>' . $row['Author_Name'] . '</td>';
                                            echo '<td>' . $row['Description'] . '</td>';
                                            echo '<td>' . $row['Due_Date'] . '</td>';
                                          echo '</tr>';
                                        }
                                      // Free result set
                                      mysqli_free_result($result);
                                  }
                              } 
                              else
                              {
                                  echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                              }

                              // Close connection
                              mysqli_close($link);
                          ?>
                </table>
            </div> 
            <br>
            <div id="lateBooks">
                <!--Loaded for the libraries that this specific professor owns-->
                <h2 class="subtitle">Late Books</h2>
                <table id="libraries">
                    <tr>
                        <th>ISBN</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                    </tr>
					<?php 
                              include 'config.php';

                              $sql = " SELECT * FROM borrows WHERE Student_ID = '{$_SESSION['studentID']}' AND Due_Date < '{$borrowDate}'";
                              if($result = mysqli_query($link, $sql))
                              {
                                  if(mysqli_num_rows($result) > 0)
                                  {
                                        while($row = mysqli_fetch_array($result))
                                        {
                                          echo '<tr>';
                                            echo '<td>' . $row['ISBN'] . '</td>';
                                            echo '<td>' . $row['Borrow_Date'] . '</td>';
                                            echo '<td>' . $row['Due_Date'] . '</td>';
                                          echo '</tr>';
                                        }
                                      // Free result set
                                      mysqli_free_result($result);
                                  }
                              } 
                              else
                              {
                                  echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                              }

                              // Close connection
                              mysqli_close($link);
                          ?>
                </table>
            </div> 
            <br>

        </div>
        
	<!--Set up to check for everything. Book name, course, library name, prof, etc-->
        <div id="searchBook" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Search</h1>
                    <label for="search">Search: </label> 
                    <input type="text" id="search" name="search" placeholder="Search"></input>
                    
                    <h1>Results</h1>
                    <div class="search_results">
                        <table id="libraries">    
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Associated Course</th>
                                <th>Associated Professor</th>
                                <th>Library ID</th>
                                <th>Number Available</th>
                            </tr>
                            <?php 
                              include 'config.php';
                              
                              $search = $_SESSION["searchBar"];

                              $sql = "SELECT * FROM search WHERE ISBN LIKE '%{$search}%' OR Title LIKE '%{$search}%' OR Author_Name LIKE '%{$search}%' OR Course_ID LIKE '%{$search}%' OR Taught_By LIKE '%{$search}%' OR Library_ID LIKE '%{$search}%'";

                              if($result = mysqli_query($link, $sql))
                              {
                                  if(mysqli_num_rows($result) > 0)
                                  {
                                        while($row = mysqli_fetch_array($result))
                                        {
                                          echo '<tr>';
                                            echo '<td>' . $row['ISBN'] . '</td>';
                                            echo '<td>' . $row['Title'] . '</td>';
                                            echo '<td>' . $row['Author_Name'] . '</td>';
                                            echo '<td>' . $row['Course_ID'] . '</td>';
                                            echo '<td>' . $row['Taught_By'] . '</td>';
                                            echo '<td>' . $row['Library_ID'] . '</td>';
                                            echo '<td>' . $row['No_Available'] . '</td>';
                                          echo '</tr>';
                                        }
                                      // Free result set
                                      mysqli_free_result($result);
                                  }
                              } 
                              else
                              {
                                  echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                              }

                              // Close connection
                              mysqli_close($link);
                          ?>
                        </table>
                    </div>

					<input type="submit" name="searchBar" value="Find Book" onclick="showPopUp1()">
                    <input type="button" value="Exit" onclick="document.getElementById('searchBook').style.display = 'none'">
                
                </div>
            </form>
        </div>

        <div id="borrowBook" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Take out a book!</h1>
                    <select id="bookID" name="bookID">
                            <?php 
                         	include 'config.php';
                         	
                            $sql = "SELECT ISBN FROM book";

                            if($result = mysqli_query($link, $sql))
                            {
                                if(mysqli_num_rows($result) > 0)
                                {
                                      while($row = mysqli_fetch_array($result))
                                      {
                                      	  echo '<option value="' . $row['ISBN'] . '">' . $row['ISBN'] . '</option>';
                                      }
                                    // Free result set
                                    mysqli_free_result($result);
                                }
                            } 
                            else
                            {
                                echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                            }

                            // Close connection
                            mysqli_close($link);
                        ?>

                    <input type="submit" name="borrowBook" value="Borrow Book" onclick="showPopUp3()">
                    <input type="button" value="Exit" onclick="document.getElementById('borrowBook').style.display = 'none'">
                
                </div>
            </form>
        </div>

         <div id="returnBook" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Return a book!</h1>
                    
                    <label for="rbookID">ISBN </label> 
                    <select id="rbookID" name="rbookID">
                            <?php 
                         	include 'config.php';
                         	
                            $sql = "SELECT ISBN FROM borrows WHERE Student_ID = '{$_SESSION["studentID"]}'";

                            if($result = mysqli_query($link, $sql))
                            {
                                if(mysqli_num_rows($result) > 0)
                                {
                                      while($row = mysqli_fetch_array($result))
                                      {
                                      	  echo '<option value="' . $row['ISBN'] . '">' . $row['ISBN'] . '</option>';
                                      }
                                    // Free result set
                                    mysqli_free_result($result);
                                }
                            } 
                            else
                            {
                                echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                            }

                            // Close connection
                            mysqli_close($link);
                        ?>
                    </select>
                   

                    <input type="submit" name="returnBook" value="Return Book" onclick="showPopUp4()">
                    <input type="button" value="Exit" onclick="document.getElementById('returnBook').style.display = 'none'">
                
                </div>
            </form>
        </div>


		<div id="editProfile" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1> Edit Profile!</h1>
                    
                      <label for="fname">First Name: </label> 
                      <input type="text" id="fname" name="fname" placeholder="John" value = "<?php echo $_SESSION["firstName"]; ?>"></input>
                    
                      <label for="lname">Last Name: </label> 
                      <input type="text" id="lname" name="lname" placeholder="Doe" value = "<?php echo $_SESSION["lastName"]; ?>" ></input>
                    
                      <label for="email">Email: </label> 
                      <input type="text" id="email" name="email" placeholder="example@email.com" value = "<?php echo $_SESSION["email"]; ?>" ></input>
                      
                    <label for="major">Major: </label> 
                    <input type="text" id="major" name="major" placeholder="Program of Study" value = "<?php echo $_SESSION["major"]; ?>" ></input>
                    
                    <input type="submit" name="editProfile" value="Save Profile" onclick="showPopUp2()">
                    <input type="button" value="Exit" onclick="document.getElementById('editProfile').style.display = 'none'">
                
                </div>
            </form>
        </div>
        
       
      <script>
            var popUp1 = document.getElementById('searchBook');
            var popUp2 = document.getElementById('editProfile');
            var popUp3 = document.getElementById('borrowBook');
            var popUp4 = document.getElementById('returnBook');
            var navbar = document.getElementById('navBox');
            var dropdown = document.getElementById('dropdown');
            var sticky = navbar.offsetTop;
            window.onmousedown = function(event) {
                if (event.target == popUp2) {
                    popUp2.style.display = "none";
                }
                if (event.target == popUp1) {
                    popUp1.style.display = "none";
                }
                if (event.target == popUp3) {
                    popUp3.style.display = "none";
                } 
                if (event.target == popUp4) {
                    popUp4.style.display = "none";
                } 
            }
            
           function showPopUp1() {
           		popUp1.display = "block";
                localStorage.setItem('popUp1Show', 'true');
           }
           function showPopUp2() {
           		popUp2.display = "block";
                localStorage.setItem('popUp2Show', 'true');
           }
           function showPopUp3() {
           		popUp3.display = "block";
                localStorage.setItem('popUp3Show', 'true');
           }
           function showPopUp4() {
           		popUp4.display = "block";
                localStorage.setItem('popUp4Show', 'true');
           }
           
           
           function load() {
           		var popUp1Show = localStorage.getItem('popUp1Show');
                var popUp2Show = localStorage.getItem('popUp2Show');
                var popUp3Show = localStorage.getItem('popUp3Show');
                var popUp4Show = localStorage.getItem('popUp4Show');
                
                if(popUp1Show === 'true') {
                	popUp1.style.display = "block";
                    localStorage.removeItem('popUp1Show');
                  
                } 
                if(popUp2Show === 'true') {
                	popUp2.style.display = "block";
                    localStorage.removeItem('popUp2Show');
                  
                } 
                if(popUp3Show === 'true') {
                	popUp3.style.display = "block";
                    localStorage.removeItem('popUp3Show');
                  
                } 
                if(popUp4Show === 'true') {
                	popUp4.style.display = "block";
                    localStorage.removeItem('popUp4Show');
                  
                } 
                
           } 
           
            window.onload = load;
           
           
            window.onscroll = function() {
                if (window.pageYOffset >= sticky) {
                    navbar.classList.add("sticky")
                } else {
                    navbar.classList.remove("sticky");
                } 
            }

      </script>
    </body>
</html>
