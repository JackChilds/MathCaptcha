<?php 

$db_table = 'captcha';

$bgColor = [212,212,212];
$textColor = [38,38,38];

$autoDeleteRecords = true; // Can be useful if you want to auto delete records from the database after 24 hours of their creation. This is very helpful especially if you are unable to setup cron jobs or web crons on your website hosting.

function connectDB() {
    // CHANGE THIS ------------------
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "math_captcha";
    $port = "8889";
    // ------------------------------

    $c = new mysqli($servername, $username, $password, $dbname, $port);
    if ($c->connect_error) {
        giveOutput('MYSQL Error');
    }
    return $c;
}

function verifyID($conn, $i) {
    global $db_table;
    $sql = "SELECT * FROM `{$db_table}` WHERE `uid`=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $i);

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function storeID($conn, $i) {
    global $db_table;
    $sql = "INSERT INTO `{$db_table}` (`uid`, `created`) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $t = time();
    $stmt->bind_param('si', $i, $t);

    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function generatePuzzle($conn, $i) {
    $operations = ['+', '-', 'x'];

    $chosenO = $operations[rand(0,2)];

    if ($chosenO === '+') {
        $chosen1 = [1,2,3,4,5,6,7,8,9,10,11,12][rand(0,11)];
        $chosen2 = [1,2,3,4,5,6,7,8,9,10,11,12][rand(0,11)];
        $answer = $chosen1 + $chosen2;
    } else if ($chosenO === '-') {
        $chosen1 = [7,8,9,10,11,12][rand(0,5)];
        $chosen2 = [1,2,3,4,5,6,7][rand(0,6)];
        $answer = $chosen1 - $chosen2;
    } else {
        $chosen1 = [1,2,3,4,5,6][rand(0,5)];
        $chosen2 = [1,2,3,4,5,6][rand(0,5)];
        $answer = $chosen1 * $chosen2;
    }

    global $db_table;
    $sql = "UPDATE `{$db_table}` SET `answer`=? WHERE `uid`=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $answer, $i);
    $stmt->execute();
    $stmt->close();

    $img = imagecreate(80, 20);

    global $bgColor, $textColor;
    $textbgcolor = imagecolorallocate($img, $bgColor[0], $bgColor[1], $bgColor[2]);
    $textcolor = imagecolorallocate($img, $textColor[0], $textColor[1], $textColor[2]);

    $txt = $chosen1 . ' ' . $chosenO .  ' ' . $chosen2;
    imagestring($img, 5, 2, 2, $txt, $textcolor);
    ob_start();
    imagepng($img);

    return 'data:image/png;base64,' . base64_encode(ob_get_clean());
}

function validateAnswer($conn, $i, $a) {
    global $db_table;
    $sql = "SELECT * FROM `{$db_table}` WHERE `uid`=? AND `answer`=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $i, $a);

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->close();
        deleteId($conn, $i);
        return true;
    }
    $stmt->close();
    deleteId($conn, $i);
    return false;
}

function captcha_validate($i, $a) { // to be used from your php script instead of sending a get/post request
    $c = connectDB();
    if (verifyID($c, $i)) {
        if (validateAnswer($c, $i, $a)) {
            $c->close();
            return 1;
        } else {
            $c->close();
            return 0;
        }
    } else {
        $c->close();
        return 2;
    }
}

function deleteID($conn, $i) {
    global $db_table, $autoDeleteRecords;
    if (!$autoDeleteRecords) {return;}
    $sql = "DELETE FROM `{$db_table}` WHERE `uid`=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $i);

    $stmt->execute();

    $stmt->close();
}

function autoDelete($conn) {
    global $db_table;

    $dayAgo = time() - 86400;

    $sql = "DELETE FROM `{$db_table}` WHERE `created`<=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dayAgo);

    $stmt->execute();

    $stmt->close();
}

function generateID() {
    return md5( uniqid( rand(1000,10000) ) );
}
function giveOutput($o) {
    echo json_encode(array ("r"=>$o));
    exit();
}


if (isset($_REQUEST['o']) && !empty($_REQUEST['o'])) {
    header('Content-Type: application/json');

    switch ($_REQUEST['o']) {
        case 'uid':
            $c = connectDB();
            autoDelete($c);
            $id = generateID();
            storeID($c, $id);
            $c->close();
            giveOutput($id);
            break;
        case 'generate':
            if (isset($_REQUEST['i']) && !empty($_REQUEST['i'])) {
                $c = connectDB();
                autoDelete($c);
                if (verifyID($c, $_REQUEST['i'])) {
                    $img = generatePuzzle($c, $_REQUEST['i']);
                    $c->close();
                    giveOutput($img);
                } else {
                    $c->close();
                    giveOutput('Invalid ID');
                }
            } else {
                giveOutput('ID Not Set');
            }
            break;
        case 'validate':
            if (isset($_REQUEST['i']) && !empty($_REQUEST['i']) && (isset($_REQUEST['a']) || $_REQUEST['a'] === 0)) {
                $c = connectDB();
                autoDelete($c);
                if (verifyID($c, $_REQUEST['i'])) {
                    if (validateAnswer($c, $_REQUEST['i'], $_REQUEST['a'])) {
                        $c->close();
                        giveOutput(1);
                    } else {
                        $c->close();
                        giveOutput(0);
                    }
                } else {
                    $c->close();
                    giveOutput('Invalid ID');
                }
            } else {
                giveOutput('Params not set');
            }
            break;
        default:
            giveOutput('Error');
    }
}

?>
