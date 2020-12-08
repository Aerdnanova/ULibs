<?php
//Init session
session_start();

//Check if prof is logged in 
if(!isset($_SESSION["professorLoggedin"]) && $_SESSION["professorLoggedin"] === false)
{
	header("index.php");
    exit;
}
// Include config file
require_once "config.php";

//Add Library Variables
$lid = $location = $address = "";
$lid_err = $location_err = $address_err = "";

//Remove Library Variables
$lib_id = $lib_id_err = "";

//Add book
$isbn = $aname = $title = $desc = $course = $is_Required = "";
$isbn_err = $aname_err = $title_err = $desc_err = "";

//Remove Book
$book_id = $book_id_err = "";

if ($_POST['removeLibrary']){

	//Validate studentID
    if(empty(trim($_POST["lib_id"])))
    {
		$lib_id_err = "Please enter a library ID.";
	}
		$sql = "SELECT * FROM belongs_to WHERE Library_ID = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_lid);
            
            $param_lid = trim($_POST["lib_id"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0)
                {
                	$lib_id = trim($_POST["lib_id"]);
                }
                else
                {
                	$lib_id_err = "There are books in this library. You can't remove it.";
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
    else
    {
		$sql = "SELECT * FROM library WHERE Library_ID = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_lid);
            
            $param_lid = trim($_POST["lib_id"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0)
                {
                	$lib_id_err = "This library doesn't exist";
                }
                else
                {
                	$lib_id = trim($_POST["lib_id"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    
    
    //Check erros then we gucci to remove it into the database
    if(empty($lib_id_err)) 
    {
    	//Prepare the insert statement DELETE FROM table_name WHERE condition;
        $sql = "DELETE FROM library WHERE Library_ID = ?";
        
        $sqlTwo = "DELETE FROM owned_by WHERE Library_ID = '{$lib_id}'";
        mysqli_query($link, $sqlTwo);
        
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_lib_id);

            //set parameter variables
            $param_lib_id = $lib_id;
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
            	//Successful then go to the form
                header("location: profAccount.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
			//Close statement
            mysqli_stmt_close($stmt);
        }
        
       	//Delete from search
        $sqlTwo = "DELETE FROM owned_by WHERE Library_ID = {'$lib_id'}";
        $resultTwo = mysqli_query($link, $sqlTwo);
        
        //Successful then go to the form
        header("location: profAccount.php");
    }
    //close connection
    mysqli_close($link);
}
else if ($_POST['addLibrary']){

	//Validate studentID
    if(empty(trim($_POST["lid"])))
    {
		$lid_err = "Please enter a library ID.";
	}
    else if(strlen(trim($_POST["lid"])) != 7)
    {
		$lid_err = "Student number must follow format of Lib-###.";
	}
    else
    {
		$sql = "SELECT * FROM library WHERE Library_ID = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_lid);
            
            $param_lid = trim($_POST["lid"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	$lid_err = "This library ID is already in use.";
                }
                else
                {
                	$lid = trim($_POST["lid"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    
    //Validate location
    if(empty(trim($_POST["location"]))){
        $location_err = "Please enter a location name.";     
    }
    else
    {
        $location = trim($_POST["location"]);
    }
    //Address
    if(empty(trim($_POST["address"]))){
        $address_err = "Please enter an address.";     
    }
    else
    {
        $address = trim($_POST["address"]);
    }
	
    //Check erros then we gucci to put it into the database
    if(empty($lid_err) && empty($location_err) && empty($address_err)) 
    {
    	//Prepare the add library
        $sql = "INSERT INTO library (Library_ID, Location, Address) VALUES (?, ?, ?)";
           
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "sss", $param_lid, $param_location, $param_address);

            //set parameter variables
            $param_lid = $lid;
            $param_location = $location;
            $param_address = $address;
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
            	//Successful then go to the form
                header("location: profAccount.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }

			//Close statement
            mysqli_stmt_close($stmt);
        }
        $sqlOwns = "INSERT INTO owned_by (Library_ID, Professor_ID) VALUES (?, ?)";
        if($stmt = mysqli_prepare($link, $sqlOwns))
        {
        	mysqli_stmt_bind_param($stmt, "ss", $param_lid, $param_profid);

            //set parameter variables
            $param_lid = $lid;
            $param_profid = $_SESSION["profID"];
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
            	//Successful then go to the form
                header("location: profAccount.php");
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

else if ($_POST['addBook']){

	//Validate studentID
    if(empty(trim($_POST["isbn"])))
    {
		$isbn_err = "Please enter a valid ISBN";
	}
    else if(strlen(trim($_POST["isbn"])) != 10)
    {
		$isbn_err = "An ISBN must be 10 numbers in length.";
	}
	else
    {
		$sql = "SELECT * FROM book WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_isbn);
            
            $param_isbn = trim($_POST["isbn"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                	$isbn_err = "This ISBN is already in use.";
                }
                else
                {
                	$isbn = trim($_POST["isbn"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    
 	$library_id = trim($_POST["library_id"]);

    //Cname
    if(empty(trim($_POST["aname"]))){
        $aname_err = "Please enter an author name.";     
    }
    else
    {
        $aname = trim($_POST["aname"]);
    }
   	
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";     
    }
    else
    {
        $title = trim($_POST["title"]);
    }

    if(empty(trim($_POST["desc"]))){
        $desc_err = "Please enter a description.";     
    }
    else
    {
        $desc = trim($_POST["desc"]);
    }
    $no_available = $_POST["no_available"];   
    
    //Check erros then we gucci to remove it into the database
    if(empty($isbn_err) && empty($library_id_err) && empty($aname_err) && empty($title_err) && empty($desc_err)) 
    {
    	//Prepare the add library
        $sql = "INSERT INTO book (ISBN, Author_Name, Title, Description) VALUES (?, ?, ?, ?)";
           
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "ssss", $param_isbn, $param_aname, $param_title, $param_desc);

            //set parameter variables
            $param_isbn = $isbn;
            $param_aname = $aname;
            $param_title = $title;
            $param_desc = $desc;
            
            //Attempt to execute
            if(mysqli_stmt_execute($stmt)){
            	//Successful then go to the form
                header("location: profAccount.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }

			//Close statement
            mysqli_stmt_close($stmt);
        }
        
        $sqlOwns = "INSERT INTO is_required_for (Course_ID, ISBN, Is_Optional) VALUES ('{$_POST["course"]}','{$isbn}','{$_POST["is_Required"]}')";
        mysqli_query($link, $sqlOwns);
        
        //Add to belongs to
       	$belongsTo = "INSERT INTO belongs_to (Title, Library_ID, No_Available) VALUES ('{$title}','{$library_id}','{$no_available}')";
        mysqli_query($link, $belongsTo);
        
        $sequel = "SELECT Taught_By FROM course WHERE Course_ID = '{$_POST["course"]}'";
        $result = $link->query($sequel);

        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
            $professor = $row["Taught_By"];
          }
        }
        
        //Add to search Table
       	$searchTable = "INSERT INTO search (ISBN, Title, Author_Name, Course_ID, Taught_By, Library_ID, No_Available) VALUES ('{$isbn}', '{$title}', '{$aname}', '{$_POST["course"]}', '{$professor}', '{$library_id}','{$no_available}')";
        mysqli_query($link, $searchTable);
        
    }
        
    //close connection
    mysqli_close($link);
}
else if ($_POST['removeBook']){

	//Validate studentID
    if(empty(trim($_POST["book_id"])))
    {
		$book_id_err = "Please enter an ISBN.";
	}
    else
    {
		$sql = "SELECT * FROM book WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_book_id);
            
            $param_book_id = trim($_POST["book_id"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0)
                {
                	$book_id_err = "This book doesn't exist";
                }
                else
                {
                	$book_id = trim($_POST["book_id"]);
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
	}
    
    $sql = "SELECT * FROM borrows WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_book_id);
            
            $param_book_id = trim($_POST["book_id"]);
            
            if(mysqli_stmt_execute($stmt))
            {
            	mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0)
                {
                	$book_id = trim($_POST["book_id"]);
                }
                else
                {
                	$book_id_err = "A student is currently borrowing one of the books.";
                }
            }
            else
            {
				echo "Oops! Something on our end went wrong! Try again later!";
			}
            mysqli_stmt_close($stmt);
        }
    
    
    //Check erros then we gucci to remove it into the database
    if(empty($book_id_err)) 
    {
        //Prepare the insert statement DELETE FROM table_name WHERE condition;
        $query = "SELECT Title FROM book WHERE ISBN = '{$book_id}'";
        $result = mysqli_query($link, $query);
        
        $row = mysqli_fetch_row($result);
        
        $titleOfBook = $row[0];
        
        $sqlTwo = "DELETE FROM belongs_to WHERE Title = '{$titleOfBook}'";
        mysqli_query($link, $sqlTwo);

    	//Prepare the insert statement DELETE FROM table_name WHERE condition;
        $sql = "DELETE FROM is_required_for WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_book_id);

            //set parameter variables
            $param_book_id = $book_id;
            
            mysqli_stmt_execute($stmt);

			//Close statement
            mysqli_stmt_close($stmt);
        }
        
        //Prepare the insert statement DELETE FROM table_name WHERE condition;
        $searchDelete = "DELETE FROM search WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $searchDelete))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_book_id);

            //set parameter variables
            $param_book_id = $book_id;
            
            //Attempt to execute
            mysqli_stmt_execute($stmt);

			//Close statement
            mysqli_stmt_close($stmt);
        }
        
       
        
        
        
        
        //Prepare the insert statement DELETE FROM table_name WHERE condition;
        $sql = "DELETE FROM book WHERE ISBN = ?";
        
        if($stmt = mysqli_prepare($link, $sql))
        {
        	mysqli_stmt_bind_param($stmt, "s", $param_book_id);

            //set parameter variables
            $param_book_id = $book_id;
            
            //Attempt to execute
            mysqli_stmt_execute($stmt);

			//Close statement
            mysqli_stmt_close($stmt);
        }
        
        header("location: profAccount.php");
    }
    //close connection
    mysqli_close($link);
}
else if ($_POST['editProfile']){

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
        
        $sql = "UPDATE professor SET First_Name = '{$fname}', Last_Name = '{$lname}', Email = '{$email}', Major ='{$major}'  WHERE Professor_ID = '{$_SESSION['profID']}'";
        
        //Update Session variables accordingly
        $_SESSION["firstNameP"] = $fname;
        $_SESSION["lastNameP"] = $lname;
        $_SESSION["emailP"] = $email;
        $_SESSION["majorP"] = $major;
        
        mysqli_query($link, $sql);
        
        header("location: profAccount.php");
    //close connection
    mysqli_close($link);
}
else if ($_POST["searchBar"])
{
	$_SESSION["searchBar"] = trim($_POST["search"]);
    
    header('Location: profAccount.php');
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
            <div class="dropdown" >
                <button href="javascript:void(0)" class="dropbtn">Library Information</button>
                <div class="dropdown_content" id="dropdown">
                    <a href="#libraryList">My Libraries</a>
                    <a href="#" onclick="document.getElementById('addLibrary').style.display = 'block'">Add Library</a>
                    <a href="#" onclick="document.getElementById('removeLibrary').style.display = 'block'">Remove Library</a>
                </div>
            </div>

            <div class="dropdown"> 
                <button href="javascript:void(0)" class="dropbtn">Book Information</button>
                <div class="dropdown_content" id="dropdown">
                    <a href="#borrowedBooks">Borrowed Books</a>         
                    <a href="#addedBooks">My Books</a>
                    <a href="#booksInLibraries">My Books by Library</a>
                    
                    <a href="#" onclick="document.getElementById('addBook').style.display = 'block'">Add Book</a>
                    <a href="#" onclick="document.getElementById('removeBook').style.display = 'block'">Remove Book</a>
                </div>
            </div>

            <a href="logout.php" class="right">Logout</a>
            <a href="#" class="right" onclick="document.getElementById('searchBook').style.display = 'block'">Search</a>
            <a href="#" class="right"  onclick="document.getElementById('editProfile').style.display = 'block'">Edit Profile</a>
        </div>
            
        <!--Pages that will be switched between making hidden and not hidden-->

							<?php 
                              include 'config.php';

                              $sql = "SELECT COUNT(ISBN) c FROM borrows";
                              $result = mysqli_query($link, $sql);
                              $row = mysqli_fetch_assoc($result);

                              $sqlTwo = "SELECT COUNT(DISTINCT Student_ID) n FROM borrows";
                              $resultTwo = mysqli_query($link, $sqlTwo);
                              $rowTwo = mysqli_fetch_assoc($resultTwo);
                              
                              // Close connection
                              mysqli_close($link);
                          ?>


        <div class="mainContent">
            <div class="profileInformation">
                <h2 class="welcome">Welcome back <?php echo $_SESSION["firstNameP"] . " " . $_SESSION["lastNameP"];?>!</h2>
                <h3 class="welcome">You have <?php echo $row['c'];?> books being borrowed from <?php echo $rowTwo['n'];?> student(s)! The following is your updated information!</h3>
            </div>
            
            <div id="libraryList">
                <!--Loaded for the libraries that this specific professor owns-->
                <h2 class="subtitle">My Libraries</h2>
                <table id="libraries">
                    <tr>
                        <th>Library ID</th>
                        <th>Location</th>
                        <th>Address</th>
                    </tr>
						<?php 
                     	include 'config.php';
                        
                        $sql = "SELECT * FROM library INNER JOIN owned_by ON library.Library_ID = owned_by.Library_ID WHERE owned_by.Professor_ID = '{$_SESSION['profID']}'";

 						if($result = mysqli_query($link, $sql))
                        {
                            if(mysqli_num_rows($result) > 0)
                            {
                                  while($row = mysqli_fetch_array($result))
                                  {
                                    echo '<tr>';
                                      echo '<td>' . $row['Library_ID'] . '</td>';
                                      echo '<td>' . $row['Location'] . '</td>';
                                      echo '<td>' . $row['Address'] . '</td>';
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
            <!--For every library that is owned, print out the books-->
            <div id="booksInLibraries"> 
                <h2 class="subtitle">Books by Libraries</h2> 
                <table id="libraries">
                    <tr>
                        <th>Library ID</th>
                        <th>Book List</th> <!--List all available by title-->
                        <th>Number Available</th>
                    </tr>
					<?php 
                    	require 'config.php';
                    
                        $sql = "SELECT Title, Library_ID, No_Available FROM belongs_to ORDER BY Library_ID";

 						if($result = mysqli_query($link, $sql))
                        {
                            if(mysqli_num_rows($result) > 0)
                            {
                                  while($row = mysqli_fetch_array($result))
                                  {
                                    echo '<tr>';
                                      echo '<td>' . $row['Library_ID'] . '</td>';
                                      echo '<td>' . $row['Title'] . '</td>';
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
            <br>
            <div id="addedBooks">
                <h2 class="subtitle">My Books</h2>
                <table id="libraries">
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Description</th>
                    </tr>
                     <?php 
                     	include 'config.php';
                        
                        $sql = "SELECT * FROM book";

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
            <div id="borrowedBooks">
                <h2 class="subtitle">Borrowed Books</h2>
                <table id="libraries">
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Student Email</th>
                        <th>Book ID</th>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>   <!--Due date is a month after the borrow-->
                    </tr>
					<?php 
                              include 'config.php';

                              $sql = "SELECT * FROM ((student INNER JOIN borrows ON student.Student_ID = borrows.Student_ID) INNER JOIN book ON borrows.ISBN = book.ISBN)";
                              if($result = mysqli_query($link, $sql))
                              {
                                  if(mysqli_num_rows($result) > 0)
                                  {
                                        while($row = mysqli_fetch_array($result))
                                        {
                                          echo '<tr>';
                                            echo '<td>' . $row['First_Name'] . " " . $row['Last_Name'] . '</td>';
                                            echo '<td>' . $row['Student_ID'] . '</td>';
                                            echo '<td>' . $row['Email'] . '</td>';
                                            echo '<td>' . $row['ISBN'] . '</td>';
                                            echo '<td>' . $row['Title'] . '</td>';
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

					<input type="submit" name="searchBar" value="Find Book" onclick="showPopUp4()">
                    <input type="button" value="Exit" onclick="document.getElementById('searchBook').style.display = 'none'">
                
                </div>
            </form>
        </div>
        	
        <div id="removeBook" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Remove Book</h1>
                    
                    <div class="form-group <?php echo (!empty($book_id_err)) ? 'has-error' : ''; ?>">
                      <label for="book_id">ISBN: </label> 
                      <input type="text" id="book_id" name="book_id" placeholder="ISBN"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $book_id_err; ?></span><br>
                    </div>
                    
                    <input type="submit" name="removeBook" value="Remove Book" onclick="showPopUp5()">
                    <input type="button" value="Exit" onclick="document.getElementById('removeBook').style.display = 'none'">
                
                </div>
            </form>
        </div>
        
        <div id="removeLibrary" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Remove Library</h1>
                    
                    <div class="form-group <?php echo (!empty($lib_id_err)) ? 'has-error' : ''; ?>">
                    	<label for="lib_id">Library ID: </label> 
                    	<input type="text" id="lib_id" name="lib_id" placeholder="lib id" value="<?php echo $lib_id; ?>"></input>
                    	<span class="help-block" style="color:red;font-size:small;"><?php echo $lib_id_err; ?></span><br>
                    </div>
                     
                    <input type="submit" name="removeLibrary" value="Remove Library" onclick="showPopUp6()">
                    <input type="button" value="Exit" onclick="document.getElementById('removeLibrary').style.display = 'none'">
                
                </div>
            </form>
        </div>
        
		<div id="editProfile" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1> Edit Profile!</h1>
                    
                      <label for="fname">First Name: </label> 
                      <input type="text" id="fname" name="fname" placeholder="John" value = "<?php echo $_SESSION["firstNameP"]; ?>"></input>
                    
                      <label for="lname">Last Name: </label> 
                      <input type="text" id="lname" name="lname" placeholder="Doe" value = "<?php echo $_SESSION["lastNameP"]; ?>" ></input>
                    
                      <label for="email">Email: </label> 
                      <input type="text" id="email" name="email" placeholder="example@email.com" value = "<?php echo $_SESSION["emailP"]; ?>" ></input>
                      
                    <label for="major">Major: </label> 
                    <input type="text" id="major" name="major" placeholder="Program of Study" value = "<?php echo $_SESSION["majorP"]; ?>" ></input>
                    
                    <input type="submit" name="editProfile" value="Save Profile" onclick="showPopUp2()">
                    <input type="button" value="Exit" onclick="document.getElementById('editProfile').style.display = 'none'">
                
                </div>
            </form>
        </div>
        
        <div id="addLibrary" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Library Information</h1>
                    
                    <div class="form-group <?php echo (!empty($lid_err)) ? 'has-error' : ''; ?>">
                      <label for="lid">Library ID: </label> 
                      <input type="text" id="lid" name="lid" placeholder="Lib-###" value="<?php echo $lid; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $lid_err; ?></span><br>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($location_err)) ? 'has-error' : ''; ?>">
                      <label for="location">Location: </label> 
                      <input type="text" id="location" name="location" placeholder="Building Name" value="<?php echo $location; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $location_err; ?></span><br>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($address_err)) ? 'has-error' : ''; ?>">
                      <label for="address">Address: </label> 
                      <input type="text" id="address" name="address" placeholder="1234 Apple Lane" value="<?php echo $address; ?>"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $address_err; ?></span><br>
                    </div>
                    
                    <!--Belongs to library will be populated with id of professor-->
                    <input type="submit" name="addLibrary" value="Add Library" onclick="showPopUp1()">
                    <input type="button" value="Exit" onclick="document.getElementById('addLibrary').style.display = 'none'">
                
                </div>
            </form>
        </div>
        
		<div id="addBook" class="popUp">
            <form class="popUpContent" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="signContainer">
                    <h1>Book Information</h1>
                    
                    <div class="form-group <?php echo (!empty($isbn_err)) ? 'has-error' : ''; ?>">
                      <label for="isbn">ISBN: </label> 
                      <input type="text" id="isbn" name="isbn" placeholder="##########"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $isbn_err; ?></span><br>
                    </div>
                    
                    <label for="library_id">Belongs to Library: </label> 
                    <select id="library_id" name="library_id">
                    		
                            <?php 
                         	include 'config.php';
                         	
                            $sql = "SELECT Library_ID FROM library";

                            if($result = mysqli_query($link, $sql))
                            {
                                if(mysqli_num_rows($result) > 0)
                                {
                                      while($row = mysqli_fetch_array($result))
                                      {
                                      	  echo '<option value="' . $row['Library_ID'] . '">' . $row['Library_ID'] . '</option>';
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
                    
                    <div class="form-group <?php echo (!empty($aname_err)) ? 'has-error' : ''; ?>">
                      <label for="aname">Author Name: </label> 
                      <input type="text" id="aname" name="aname" placeholder="Cassy Clare"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $aname_err; ?></span><br>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                      <label for="title">Title: </label> 
                      <input type="text" id="title" name="title" placeholder="The Book Title"></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $title_err; ?></span><br>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($desc_err)) ? 'has-error' : ''; ?>">
                      <label for="desc">Description: </label> 
                      <input type="text" id="desc" name="desc" placeholder="This book is about ..."></input>
                      <span class="help-block" style="color:red;font-size:small;"><?php echo $desc_err; ?></span><br>
                    </div>
                    
                      <label for="no_available">Number available: </label> <br>
                      <input type="number" id="no_available" name="no_available" min="0" max="300"></input><br><br>
                    
                    <label for="course">Courses Available: </label>
                    <select id="course" name="course">
                            <?php 
                         	include 'config.php';
                         	
                            $sql = "SELECT Course_ID FROM course";

                            if($result = mysqli_query($link, $sql))
                            {
                                if(mysqli_num_rows($result) > 0)
                                {
                                      while($row = mysqli_fetch_array($result))
                                      {
                                      	  echo '<option value="' . $row['Course_ID'] . '">' . $row['Course_ID'] . '</option>';
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
                        <option value="N/A">N/A</option>
                    </select>

                    <label for="is_Required">Is Required:</label>
                    <select id="is_Required" name="is_Required">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    
                    <!--Add is optional thingy-->

                    <input type="submit" name="addBook" value="Add Book" onclick="showPopUp3()">
                    <input type="button" value="Exit" onclick="document.getElementById('addBook').style.display = 'none'">
                
                </div>
            </form>
        </div>


      <script>
            var popUp1 = document.getElementById('addLibrary');
            var popUp2 = document.getElementById('editProfile');
            var popUp3 = document.getElementById('addBook');
            var popUp4 = document.getElementById('searchBook');
            var popUp5 = document.getElementById('removeBook');
            var popUp6 = document.getElementById('removeLibrary');
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
                if (event.target == popUp5) {
                    popUp5.style.display = "none";
                }
                if (event.target == popUp6) {
                    popUp6.style.display = "none";
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
           function showPopUp5() {
           		popUp5.display = "block";
                localStorage.setItem('popUp5Show', 'true');
           }
           function showPopUp6() {
           		popUp6.display = "block";
                localStorage.setItem('popUp6Show', 'true');
           }
           
           
           function load() {
           		var popUp1Show = localStorage.getItem('popUp1Show');
                var popUp2Show = localStorage.getItem('popUp2Show');
                var popUp3Show = localStorage.getItem('popUp3Show');
                var popUp4Show = localStorage.getItem('popUp4Show');
                var popUp5Show = localStorage.getItem('popUp5Show');
                var popUp6Show = localStorage.getItem('popUp6Show');
                
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
                if(popUp5Show === 'true') {
                	popUp5.style.display = "block";
                    localStorage.removeItem('popUp5Show');
                  
                } 
                if(popUp6Show === 'true') {
                	popUp6.style.display = "block";
                    localStorage.removeItem('popUp6Show');
                  
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
