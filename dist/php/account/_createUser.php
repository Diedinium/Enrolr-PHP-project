<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Return error if user is not authenticated or is not admin
if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If required values aren't set, return error.
    if (
        !isset($_POST['createEmail']) || !isset($_POST['createPassword']) || !isset($_POST['createPasswordConfirm']) ||
        !isset($_POST['createFirstName']) || !isset($_POST['createLastName']) || !isset($_POST['createJobRole'])
    ) {
        echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
        die;
    } else {
        $createEmail = $_POST['createEmail'];
        $createPassword = $_POST['createPassword'];
        $createPasswordConfirm = $_POST['createPasswordConfirm'];
        $createFirstName = $_POST['createFirstName'];
        $createLastName = $_POST['createLastName'];
        $createJobRole = $_POST['createJobRole'];
        if (!isset($_POST['createIsAdmin'])) {
            $createIsAdmin = false;
        } else {
            $createIsAdmin = true;
        }

        // If passwords do not match, return error.
        if ($createPassword !== $createPasswordConfirm) {
            echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
            die;
        }

        try {
            // Insert account into DB and get Id.
            $insertId = $account->addAccount($createEmail, $createPassword, $createFirstName, $createLastName, $createJobRole, $createIsAdmin);

            // Return details of account in post back.
            echo json_encode([
                'success' => 1,
                'message' => "User added with email $createEmail",
                "details" => [
                    "id" => $insertId,
                    "firstName" => $createFirstName,
                    "lastName" => $createLastName,
                    "email" => $createEmail,
                    "jobTitle" => $createJobRole,
                    "isAdmin" => $createIsAdmin
                ]
            ]);
            die;
        } catch (Exception $ex) {
            echo json_encode(["success" => 0, "message" => $ex->getMessage()]);
            die;
        }
    }
}
