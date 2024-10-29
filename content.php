
<h1>View Registered Users</h1>
<table>
    <thead>
        <tr>
            <th>Serial No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($userResult) > 0) {
            $serialNo = 1; // Initialize serial number
            while ($row = mysqli_fetch_assoc($userResult)) {
                $userName = htmlspecialchars($row["name"]);
                $userEmail = htmlspecialchars($row["email"]);
                $userPhone = htmlspecialchars($row["phone"]);
                $userAddress = htmlspecialchars($row["address"]);
                $status = htmlspecialchars($row["status"]);
                $userId = htmlspecialchars($row["lid"]); // Used for activation/deactivation

                $statusText = $status == 0 ? "Active" : "Inactive";
                $statusAction = $status == 0 ? "deactivate" : "activate";
                $btnClass = $status == 0 ? "deactivate" : "activate";
                $btnText = $status == 0 ? "Deactivate" : "Activate";

                echo "<tr>
                        <td>$serialNo</td> <!-- Display serial number -->
                        <td>$userName</td>
                        <td>$userEmail</td>
                        <td>$userPhone</td>
                        <td>$userAddress</td>
                        <td>$statusText</td>
                        <td><a href='view_users.php?action=$statusAction&id=$userId' class='btn $btnClass'>$btnText</a></td>
                      </tr>";

                $serialNo++; // Increment serial number
            }
        } else {
            echo "<tr><td colspan='7'>No users found</td></tr>";
        }
        ?>
    </tbody>
</table>
