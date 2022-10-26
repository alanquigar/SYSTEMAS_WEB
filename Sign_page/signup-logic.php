<?php
session_start();
require '../config/database.php';
  //Get signup form data if signup button was clicked
  if (isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    //Validate input values
    $contador=0;
    if(!$firstname) {
      echo "Agregar Nombre";
      $contador++;
    } elseif(!$lastname) {
      echo "Agregar Apellido";
      $contador++;
    } elseif(!$email) {
      echo "Agregar Correo Electronico";
      $contador++;
    } elseif(strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
      echo "La contaseña es muy corta";
      $contador++;
    } /*elseif($confirmpassword != $createpassword) {
      $_SESSION['signup'] = "Passwords do not match";
    }*/ elseif(!$avatar['name']) {
      echo "Agregar foto de perfil";
      $contador++;
    } else {
      /* ! Check if passwords don't match*/
      if($confirmpassword != $createpassword) {
        echo "Contraseñas no coinciden";
        $contador++;
      } else {
        // Hash password
        $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);
      
        // check if username or email already exist in database
        $user_check_query = "SELECT *  FROM users WHERE email='$email'";
        $user_check_result = mysqli_query($connection, $user_check_query);
        if (mysqli_num_rows($user_check_result) > 0) {
          echo "El correo ya existe en la base de datos.";
        } else {
          // WORK ON AVATAR
          // Rename avatr
          $time = time(); // Make ecah image name unique using current timestamp
          $avatar_name = $time . $avatar['name'];
          $avatar_tmp_name = $avatar['tmp_name'];
          $avatar_destination_path = 'img/' . $avatar_name;

          //Make sure file is an image
          $allowed_files = ['png', 'jpg', 'jpeg'];
          $extension = explode('.', $avatar_name);
          $extension = end($extension);
          if (in_array($extension, $allowed_files)) {
            // Make sure image is not too large (1mb)
            if ($avatar['size'] < 1000000) {
              // Upload avatar
              move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
            } else{
              echo "Imagen demasiado pesada";
              $contador++;
            }
          } else {
            echo "La imagen debe ser png, jpg o jpeg";
            $contador++;
          }
        }
      }
    }
    // Redirect back to signup page if there was any problem
    if ($contador>0) {
      echo "Vuelve atras y soluciona el problema";
    } else {
      // Insert new user into users table
      // $insert_user_query = "INSERT INTO users (firstname, lastname, username, email, password, avatar, is_admin) VALUES ('$firstname', '$lastname', '$username', '$email', '$hashed_password', '$avatar_name', 0)";
      $insert_user_query = "INSERT INTO users SET firstname='$firstname', lastname='$lastname', email='$email', password='$hashed_password', avatar='$avatar_name', is_admin=0";
      $insert_user_result = mysqli_query($connection, $insert_user_query);
      if(!mysqli_errno($connection)) {
        // Redirect to login page with success message
        $_SESSION['signup-success'] = "Registration successfull. Please Login";
        header('location: '. ROOT_URL . 'Login_page/login.html');
        die();
      }
    }
  } else {
    // If button wasn't clicked, bounce back to signup page
    header('location: ' . ROOT_URL . 'signup.php');
    die();
  }
?>