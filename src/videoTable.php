<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Connect to database
// NOTE: PASSWORD must be replaced with ONID DB password
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "blackwlu-db", PASSWORD, "blackwlu-db");

// Check connection
if(!$mysqli || $mysqli->connect_errno)
{
    echo "Connection error ".$mysqli->connect_errno." ".$mysqli->connect_error;
}

// Add video (only if coming from add form)
if(!empty($_POST))
{
  // Make sure input is valid
  if(empty($_POST['name']))
  {
      echo "The video needs a title. Please try again.";
  }
  elseif(strlen($_POST['name']) > 255)
  {
      echo "Title too long. Please try again. Limit is 255 characters.";
  }
  elseif(strlen($_POST['category']) > 255)
  {
      echo "Category too long. Please try again. Limit is 255 characters.";
  }
  elseif(!empty($_POST['length']) && $_POST['length'] < 0)
  {
      echo "Length must be a positive integer. Please try again.";
  }

  else
  {
      // Get variables
      $name = $_POST['name'];

      if(empty($_POST['category']))
      {
          $category = "";
      }
      else
      {
          $category = $_POST['category'];
      }

      if(empty($_POST['length']))
      {
          $length = 0;
      }
      else
      {
          $length = $_POST['length'];
      }

      // Prepare prepared statement
      if(!($stmt = $mysqli->prepare("INSERT INTO video(name, category, length) VALUES (?, ?, ?)")))
      {
          echo "Prepare failed: (".$mysqli->errno.")".$mysqli->error;
      }

      // Bind and execute prepared statement
      if(!$stmt->bind_param("ssi", $name, $category, $length))
      {
          echo "Binding parameters failed: (".$stmt->errno.") ".$stmt->error;
      }
      if(!$stmt->execute())
      {
          echo "Execute failed: (".$stmt->errno.")".$stmt->error;
      }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="stylesheet.css" rel="stylesheet" type="text/css">
    <title>Assignment 4 Part 2</title>
</head>
<body>
  <h2>Video Rental System</h2>
  <?php
    // Get table data
    $query = "SELECT * FROM video";
    $result = mysqli_query($mysqli, $query);

    // Get filter, if any
    if(isset($_GET['catMenu']) && $_GET['catMenu'] != "any")
    {
      $catFilter = $_GET['catMenu'];
    }
    else
    {
      $catFilter = NULL;
    }

    // Create HTML table
    echo "<table>";
    echo "<thead><tr><th>Title</th><th>Category</th><th>Length</th><th>Availability</th><th></th><th></th></tr></thead>";
    echo "<tbody>";

    while($row = mysqli_fetch_array($result))
    {
      if($row['rented'])
      {
        $rented = "checked out";
      }
      else
      {
        $rented = "available";
      }

      if($catFilter == NULL || $row['category'] == $catFilter)
      {
        if($rented == "checked out")
        {
          echo "<tr><td>".$row['name']."</td>
          <td>".$row['category']."</td>
          <td>".$row['length']."</td>
          <td>".$rented."</td>
          <td><form id='delete' method='POST' action='deleteVideo.php'><input type='hidden' name='id' value='".$row['id']."'><input type='submit' value='delete'></form></td>
          <td><form id='checkinout' method='POST' action='checkInOut.php'><input type='hidden' name='id' value='".$row['id']."'><input type='hidden' name='inOrOut' value='in'><input type='submit' value='check in'></form></td></tr>";
        }
        else
        {
          echo "<tr><td>".$row['name']."</td>
          <td>".$row['category']."</td>
          <td>".$row['length']."</td>
          <td>".$rented."</td>
          <td><form id='delete' method='POST' action='deleteVideo.php'><input type='hidden' name='id' value='".$row['id']."'><input type='submit' value='delete'></form></td>
          <td><form id='checkinout' method='POST' action='checkInOut.php'><input type='hidden' name='id' value='".$row['id']."'><input type='hidden' name='inOrOut' value='out'><input type='submit' value='check out'></form></td></tr>";
        }
      }
    }
    echo "</tbody>";
    echo "</table>";

    // Get unique categories
    $query = "SELECT DISTINCT category FROM video";
    $result = mysqli_query($mysqli, $query);

    echo "<div><span id='viewCat'>View category: </span><form id='chooseCat' method='GET' action='videoTable.php'><select name='catMenu'><option value='any'>All Categories</option>";

    while($fetchedCats = mysqli_fetch_array($result))
    {
      for($i = 0; $i < count($fetchedCats); $i++)
      {
        if(isset($fetchedCats[$i]) && !empty($fetchedCats[$i]))
        {
          echo "<option value='".$fetchedCats[$i]."'>".$fetchedCats[$i]."</option>";
        }
      }
    }

    echo "</select><input type='submit' value='Filter'></form><form action='deleteAll.php'><input type='submit' value='Delete all videos'></form></div>";

    mysqli_close($mysqli);
  ?>
  <br>
  <h3>Add a Video</h3>
    <form id="addVideoForm" method="POST" action="videoTable.php">
      <input type="text" name="name" placeholder="Title"><br>
      <input type="text" name="category" placeholder="Category"><br>
      <input type="number" name="length" placeholder="Length"><br>
      <input type="submit" value="Add Video">
    </form>
</body>
</html>