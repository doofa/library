<?php
    include_once "connection.php";
?>
<html>
    <head>
        <title>Library</title>
    </head>
    <body>
        <p><b>PLEASE SELECT AN OPTION</p>
        <select onchange="window.location = this.options[this.selectedIndex].value;">
             <option>-Select-</option>
            <option value="index.php?action=list">List All Books</option>
            <option value="index.php?action=issue">Issue a Book</option>
            <option value="index.php?action=return">Return a Book</option>
            <option value="index.php?action=admin">Admin Panel</option>
            <option value="index.php?action=user">List Users</option>
        </select>
        <br /><br />
            <?php     
        if(isset($_GET['success']) && $_GET['success']!=''){
            echo $_GET['success'];
        }
        //This is for adding a book into database       
        if(isset($_POST['bookname']) && $_POST['bookname']!='' && isset($_POST['copies']) && $_POST['copies']!='' && isset($_POST['author']) && $_POST['author']!=''){
            $bookname=$_POST['bookname'];
       
            $author=$_POST['author'];
            $copies=$_POST['copies'];
            $query="INSERT INTO books(name,author,copies) VALUES('".$bookname."','".$author."','".$copies."')";
            if(!mysqli_query($link, $query))
            {
                echo $error= 'Error in Adding a Book.';
                die($link->error);
            }
            $success="&success='Book has been added succefully'";
             header("location:index.php?action=admin".$success);
        }
        
        
        
        //Add a user
         if(isset($_POST['userid']) && $_POST['userid']!='' && isset($_POST['type']) && $_POST['type']!='' && isset($_POST['avail']) && $_POST['avail']!=''){          
             $userid=$_POST['userid'];
            $avail=$_POST['avail'];
            $type=$_POST['type'];
            $query="INSERT INTO users(userid,type,canissue,issued) VALUES('".$userid."','".$type."','".$avail."',0)";
            //die($query);
            if(!mysqli_query($link, $query))
            {
                $error="Error in insertion".$link->error;
                die($error.$link->error);
            }
           $success="&success='User has been added succefully'";
             header("location:index.php?action=admin".$success);
        }
        
        
        //This is for issuing a book.    
        if(isset($_POST['bookid']) && $_POST['bookid']!='' && isset($_POST['userid']) && $_POST['userid']!='' && isset($_POST['days']) && $_POST['days']!=''  && isset($_POST['status']) && $_POST['status']!=''){
            $bookid=$_POST['bookid'];
            $userid=$_POST['userid'];
            $days=$_POST['days'];
            $status=$_POST['status'];
            $date=date("dd-mm-yyyy HH:mm:ss");
            $query="SELECT copies FROM books WHERE bookid='".$bookid."'";
            $copies=  mysqli_query($link, $query);
             if(!$copies)
                die($link->error);
            if(mysqli_num_rows($copies)<=0)
                die("This id does not exist");          
            $copies=  mysqli_fetch_array($copies);
            if($copies['copies']<=0)
            {
                echo $error="Books are not available.";
                die($error);                
            }
            $query="SELECT canissue FROM users WHERE userid=".$userid;
            $no=  mysqli_query($link, $query);
            if(!$no)
                 die($link->error);
            if(mysqli_num_rows($no)<=0)
                die("This user id does not exist");          
            $can=  mysqli_fetch_array($no);
            if($can['canissue']<=0)
            {
               echo $error="Books can not be issued to this user";
               die($error);              
            }
            $query="INSERT INTO issued(bookid,userid,date,days,status) values(".$bookid.",".$userid.",'".$date."','".$days."','".$status."')";
            if(!mysqli_query($link, $query))
            {             
                echo $error="Error in issueting";die("insert Error".$link->error);
            }           
            $query="UPDATE books SET copies=copies-1 WHERE bookid=".$bookid;
            if(!mysqli_query($link, $query))
            {
                echo $error= 'Error in issueing updating';
                die("Update Error".$link->error);
            }
            $query="UPDATE users SET canissue=canissue-1 WHERE userid=".$userid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing';
                die("Update in user table Error".$link->error);
            }
            else
            {
                $query="UPDATE users SET issued=issued+1 WHERE userid=".$userid;
                //die($query);
                if(!mysqli_query($link, $query))
                {
                    $error= 'Error in issueing';
                    die("Update in user table Error".$link->error);
                }
            }
            $success="&success='Book has been issued succefully'";
             header("location:index.php?action=issue".$success);
        }
        //This is for returning book
        else if(isset($_POST['bookid']) && $_POST['bookid']!='' && isset($_POST['userid']) &&$_POST['userid']!=''){
            $bookid=$_POST['bookid'];
            $userid=$_POST['userid'];
            $query="SELECT * FROM issued WHERE bookid='".$bookid."' AND userid='".$userid."'";
            $res=mysqli_query($link, $query);
            if(!$res)
            {
                $error='Not issued to this user';
                die("Delete Error".$link->error);
               // header("location:index.php?action=return");
            }
            if(mysqli_num_rows($res)<=0)
                die("This book has not been issued to this user");
            $query="DELETE FROM issued WHERE bookid='".$bookid."' AND userid='".$userid."'";
            if(!mysqli_query($link, $query))
            {
                $error='Not issued to this user';
                die("Delete Error".$link->error);
               // header("location:index.php?action=return");
            }
           $query="UPDATE users SET canissue=canissue+1 WHERE userid=".$userid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in returning';
                die("Update in user table Error".$link->error);
            }
            else
            {
                $query="UPDATE users SET issued=issued-1 WHERE userid=".$userid;
                //die($query);
                if(!mysqli_query($link, $query))
                {
                    $error= 'Error in issueing';
                    die("Update in user table Error".$link->error);
                }
            }
            $query="UPDATE books SET copies=copies+1 WHERE bookid='".$bookid."'";
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing';
                die("Update Error in books".$link->error);
            }

            $success="&success='Book has been returned succefully'";
             header("location:index.php?action=return".$success);
        }

        //User action
        if(isset($_GET['action']) && $_GET['action']!=''){
         $action=$_GET['action'];
            
         //If user wants to list all books.
            if($action=='list')
            {
                $query="SELECT * FROM books";
                $books=mysqli_query($link, $query);
                $html="<br /><br /><br /><table>"
                        . "<thead>"
                        . "<tr>"
                        . "<td><b>BOOK ID</td>"
                        . "<td><b>BOOK NAME</td>"
                        . "<td><b>BOOK AUTHOR</td>"
                        . "<td><b>NO OF COPIES AVAILABLE</td>"
                        . "</tr>"
                        . "</thead>";
                echo $html;
                while($book=mysqli_fetch_array($books)){
                ?>
             <tr>
                        <td><?=$book['bookid']?></td>
                       <td><?=$book['name']?></td>
                        <td><?=$book['author']?></td>
                        <td><?=$book['copies']?></td>
             </tr>
                <?php }
            }
             //If user wants to list all users.
            if($action=='user')
            {
                $query="SELECT * FROM users";
                $users=mysqli_query($link, $query);
                $html="<br /><br /><br /><table>"
                        . "<thead>"
                        . "<tr>"
                        . "<td><b>No.</td>"
                        . "<td><b>User ID</td>"
                        . "<td><b>User Type</td>"
                        . "<td><b>Books Can Be Issued</td>"
                        . "<td><b>Issued Books</td>"
                        . "</tr>"
                        . "</thead>";
                echo $html;
                while($user=mysqli_fetch_array($users)){
                ?>
             <tr>
                        <td><?=$user['id']?></td>
                       <td><?=$user['userid']?></td>
                       <td><?=$user['type']?></td>
                        <td><?=$user['canissue']?></td>
                        <td><?=$user['issued']?></td>
             </tr>
                <?php }
            }
             //If user wants to issue a book.
        else if($action=='issue'){
            ?>
            <br /><br /><br />
            <span><b>Issue A Book</span><br /><br />
                <table>
            <form action="index.php" method="post">
              <tr><td>  Enter Book Id</td><td><input type="number" min="1" name="bookid"></td></tr>
                <tr><td>Enter User Id</td><td><input type="number" name="userid" placeholder="Ex-20130154"></td></tr>
               <tr><td>Enter Number of Days</td><td><input type="number" name="days"></td></tr>
               <tr><td>Enter Status</td><td><input type="text" name="status"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Issue"></td></tr>
            </form>
                </table>
                
               <?php }
                //If user wants to return book.
            else if($action=='return'){         
                       ?>
            <br /><br /><br />
            <span><b>Return A Book</span><br /><br />
                <table>
            <form action="index.php" method="post">
              <tr><td>  Enter Book Id</td><td><input type="number" min="1" name="bookid"></td></tr>
              
               <tr><td>Enter User Id</td><td><input type="number" name="userid" placeholder="Ex-20130154"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Return"></td></tr>
               <!--<tr><td>Enter Number of Days</td><td><input type="number" name="days"></td></tr>
               <tr><td>Enter Status</td><td><input type="text" name="status"></td></tr>-->
            </form>
                </table>
                
               <?php }
              //If admin wants to add book or student
               else if($action=='admin')
               {
                   ?>
            <br /><br /><br />
            <span>ADD A BOOK</span>
            <table>
                  <form action="index.php" method="POST">
              <tr><td>  Enter Book Name</td><td><input type="text" min="1" name="bookname"></td></tr>
              <tr><td>  Enter Book Author</td><td><input type="text" min="1" name="author"></td></tr>
              <tr><td>  Enter Book Copies</td><td><input type="number" min="1" name="copies"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Add Book"></td></tr>
            </form>
                </table>
                <span>ADD A USER</span>
                <table>
                  <form action="index.php" method="POST">
              <tr><td>  Enter User ID</td><td><input type="number" min="1" name="userid"></td></tr>
              <tr><td>  Enter Type</td><td><input type="text" min="1" name="type"></td></tr>
              <tr><td>  Enter No Books To Avail</td><td><input type="number" min="1" name="avail"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Add User"></td></tr>
            </form>
                </table>
            <?php
               }
        }
        mysqli_close($link);
               ?>
    </body>
</html>