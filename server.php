<?php
session_start();
ini_set('memory_limit', '1024M'); // or you could use 1G

// initializing variables
$username = "";
$password = "";
$errors = array(); 

//connect to the database
$db = mysqli_connect('localhost', 'root', '', 'school-library');

// Register user
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);

  // form validation: ensure that the form is correctly filled ...
  // by adding (array_push()) corresponding error unto $errors array
  if (empty($username)) { array_push($errors, "Username is required"); }
  if (empty($email)) { array_push($errors, "Email is required"); }
  if (empty($password_1)) { array_push($errors, "Password is required"); }
  if ($password_1 != $password_2) {
	array_push($errors, "The two passwords did not match. Try again!");
  }

  // first check the database to make sure 
  // a user does not already exist with the same username and/or email
  $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
  $reg_result = mysqli_query($db, $user_check_query);
  $user = mysqli_fetch_assoc($reg_result);
  
  if ($user) { // if user exists
    if ($user['username'] === $username) {
      array_push($errors, "Username already exists");
    }

    if ($user['email'] === $email) {
      array_push($errors, "email already exists");
    }
  }

  // Finally, register user if there are no errors in the form
  if (count($errors) == 0) {
  	$password = md5($password_1);//encrypt the password before saving in the database

  	$query = "INSERT INTO users (username, email, password) 
  			  VALUES('$username', '$email', '$password')";
  	mysqli_query($db, $query);
  	$_SESSION['username'] = $username;
  	header('location: index.php');
  }
}


// Login user
if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password_1']);
  
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
  
    if (count($errors) == 0) {
        $password = md5($password);
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $log_result = mysqli_query($db, $query);
        if (mysqli_num_rows($log_result) == 1) {
          $_SESSION['username'] = $username;
          $_SESSION['success'] = "You are now logged in";
          header('location: index.php');
        }else {
           
            array_push($errors, "Wrong username or password ");
        }
    }
  }



  
//Borrow Books Button
if(isset($_POST['borrow'])) {

  
  $title = $_POST['title'];
  $username = $_SESSION['username'];
  $copies = $_POST['copies'];
  $book_id = $_POST['id'];
 

  $borrowed_books =  mysqli_query($db, "SELECT * FROM `borrowed-books` WHERE user_id = '$username'");

  
  $now = time(); //current timestamp
  if($copies == 0){
    $message[] = 'This book has no available copies left now. Try again later!';
  }
  else if(mysqli_num_rows($borrowed_books) >= 3 ){
  $message[] = 'You can only borrow up to 3 books';
}else{
  $copies = $copies - 1;
  mysqli_query($db, "UPDATE `ebooks` SET `copies`= $copies WHERE id = $book_id") or die('query failed');
  mysqli_query($db, "INSERT INTO `borrowed-books`(user_id, book_id, book_name, borrow_time ) VALUES('$username', '$book_id','$title', '$now')") or die('query failed');
  $added_book_msg[] = date('Y-m-d H:i:s', $now). " ". $title;//A date like 2021-01-10

}


};





//Return Books Button
if(isset($_POST['return'])){
  
 
  $IncrementValue = 1;
  $book_name = $_POST['book_name'];
  $conn_id = $_POST['conn_id'];
  $book_id = $_POST['book_id'];

  mysqli_query($db, "UPDATE `ebooks` SET `copies`= copies +  $IncrementValue  WHERE id = $book_id") or die('query failed');
  mysqli_query($db, "DELETE FROM `borrowed-books` WHERE conn_id = '$conn_id'") or die('query failed');
  $returned_book_msg[] = 'You returned '. $book_name .' succesfully!';
}
  ?>