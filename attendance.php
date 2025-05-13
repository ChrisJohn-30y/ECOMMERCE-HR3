<?php
if (isset($_POST['employee'])) {
    $output = array('error' => false);
    include 'conn.php';
    include 'timezone.php';

    $employee = $_POST['employee'];
    $status = $_POST['status'];

    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);

    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $id = $row['id'];
        $date_now = date('Y-m-d');

        if ($status == 'in') {
            $sql = "SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now' AND time_in IS NOT NULL";
            $query = $conn->query($sql);

            if ($query->num_rows > 0) {
                $output['error'] = true;
                $output['message'] = 'You have timed in for today';
            } else {
                $sched = $row['schedule_id'];
                $lognow = date('H:i:s');
                $sql = "SELECT * FROM schedules WHERE id = '$sched'";
                $squery = $conn->query($sql);
                $srow = $squery->fetch_assoc();
                $logstatus = ($lognow > $srow['time_in']) ? 0 : 1;

                $sql = "INSERT INTO attendance (employee_id, date, time_in, status) VALUES ('$id', '$date_now', NOW(), '$logstatus')";
                if ($conn->query($sql)) {
                    $output['message'] = 'Time in: ' . $row['firstname'] . ' ' . $row['lastname'];
                    $output['reload'] = true;
                } else {
                    $output['error'] = true;
                    $output['message'] = $conn->error;
                }
            }
        } elseif ($status == 'out') {
            $sql = "SELECT *, attendance.id AS uid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id WHERE attendance.employee_id = '$id' AND date = '$date_now'";
            $query = $conn->query($sql);

            if ($query->num_rows < 1) {
                $output['error'] = true;
                $output['message'] = 'Cannot Timeout. No time in.';
            } else {
                $row = $query->fetch_assoc();
                if ($row['time_out'] != '00:00:00') {
                    $output['error'] = true;
                    $output['message'] = 'You have timed out for today';
                } else {
                    $sql = "UPDATE attendance SET time_out = NOW() WHERE id = '" . $row['uid'] . "'";
                    if ($conn->query($sql)) {
                        $output['message'] = 'Time out: ' . $row['firstname'] . ' ' . $row['lastname'];
                        $output['reload'] = true;

                        $sql = "SELECT * FROM attendance WHERE id = '" . $row['uid'] . "'";
                        $query = $conn->query($sql);
                        $urow = $query->fetch_assoc();

                        $time_in = $urow['time_in'];
                        $time_out = $urow['time_out'];

                        $sql = "SELECT * FROM employees LEFT JOIN schedules ON schedules.id=employees.schedule_id WHERE employees.id = '$id'";
                        $query = $conn->query($sql);
                        $srow = $query->fetch_assoc();

                        if ($srow['time_in'] > $urow['time_in']) {
                            $time_in = $srow['time_in'];
                        }

                        if ($srow['time_out'] < $urow['time_in']) {
                            $time_out = $srow['time_out'];
                        }

                        $time_in = new DateTime($time_in);
                        $time_out = new DateTime($time_out);
                        $interval = $time_in->diff($time_out);
                        $hrs = $interval->format('%h');
                        $mins = $interval->format('%i');
                        $mins = $mins / 60;
                        $int = $hrs + $mins;
                        if ($int > 4) {
                            $int = $int - 1;
                        }

                        $sql = "UPDATE attendance SET num_hr = '$int' WHERE id = '" . $row['uid'] . "'";
                        $conn->query($sql);
                    } else {
                        $output['error'] = true;
                        $output['message'] = $conn->error;
                    }
                }
            }
        } elseif ($status == 'breakin') {
            $sql = "SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now'";
            $query = $conn->query($sql);
            if ($query->num_rows < 1) {
                $output['error'] = true;
                $output['message'] = 'Cannot Break In. No time in.';
            } else {
                $row = $query->fetch_assoc();
                if ($row['break_in'] != '00:00:00' && $row['break_in'] != null) {
                    $output['error'] = true;
                    $output['message'] = 'You have already taken a break.';
                } else {
                    $sql = "UPDATE attendance SET break_in = NOW() WHERE id = '" . $row['id'] . "'";
                    if ($conn->query($sql)) {
                        $output['message'] = 'Break In: ' . $row['firstname'] . ' ' . $row['lastname'];
                        $output['reload'] = true;
                    } else {
                        $output['error'] = true;
                        $output['message'] = $conn->error;
                    }
                }
            }
        } elseif ($status == 'breakout') {
            $sql = "SELECT * FROM attendance WHERE employee_id = '$id' AND date = '$date_now'";
            $query = $conn->query($sql);
            if ($query->num_rows < 1) {
                $output['error'] = true;
                $output['message'] = 'Cannot Break Out. No time in.';
            } else {
                $row = $query->fetch_assoc();

                if ($row['break_in'] == '00:00:00' || $row['break_in'] == null) {
                    $output['error'] = true;
                    $output['message'] = 'You must Break In first.';
                } elseif ($row['break_out'] != '00:00:00' && $row['break_out'] != null) {
                    $output['error'] = true;
                    $output['message'] = 'You have already ended your break.';
                } else {
                    $breakInTime = new DateTime($row['break_in']);
                    $now = new DateTime();
                    $diff = $breakInTime->diff($now);
                    $elapsedMins = ($diff->h * 60) + $diff->i;

                    if ($elapsedMins < 15) {
                        $remaining = 15 - $elapsedMins;
                        $output['error'] = true;
                        $output['message'] = "Finish your break! You have $remaining minute(s) left.";
                    } else {
                        $overbreak = max(0, $elapsedMins - 15);
                        $sql = "UPDATE attendance SET break_out = NOW(), overbreak = '$overbreak' WHERE id = '" . $row['id'] . "'";
                        if ($conn->query($sql)) {
                            $output['message'] = 'Break Out: ' . $row['firstname'] . ' ' . $row['lastname'];
                            $output['reload'] = true;
                        } else {
                            $output['error'] = true;
                            $output['message'] = $conn->error;
                        }
                    }
                }
            }
        } else {
            $output['error'] = true;
            $output['message'] = 'Invalid status value.';
        }
    } else {
        $output['error'] = true;
        $output['message'] = 'Employee ID not found';
    }
}

echo json_encode($output);
?>
