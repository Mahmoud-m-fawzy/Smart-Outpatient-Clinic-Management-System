
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Medical Center</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/Mangment_panel_staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <main class="manage-staff-container">
        <div class="manage-section">
            <div class="header-actions">
                <h1>Manage Staff Members</h1>
                <div class="header-buttons">
                    <a href="add_new.php" class="add-staff-btn">
                        <i class="fas fa-user-plus"></i> Add New Staff
                    </a>
                    <a href="manager_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>


            <div class="staff-list">
                <?php if (empty($staffMembers)): ?>
                    <div class="no-staff-message">
                        <i class="fas fa-users-slash"></i>
                        <p>No staff members found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($staffMembers as $member): ?>
                        <div class="staff-card <?php echo $member['is_active'] ? 'active' : 'inactive'; ?>">
                            <div class="staff-info">
                                <div class="staff-header">
                                    <h3><?php echo htmlspecialchars($member['FN'] . ' ' . $member['LN']); ?></h3>
                                    <span class="status-badge <?php echo $member['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="staff-details">
                                    <p><strong>ID:</strong> <?php echo htmlspecialchars($member['id']); ?></p>
                                    <p><strong>Role:</strong> <?php echo htmlspecialchars($member['role']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone']); ?></p>
                                </div>
                            </div>
                            <div class="staff-actions">
                                <?php if ($member['is_active']): ?>
                                    <form method="POST" action="" class="action-form"
                                        onsubmit="return confirm('Are you sure you want to deactivate this staff member?');">
                                        <input type="hidden" name="staff_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="deactivate-btn">
                                            <i class="fas fa-user-slash"></i> Deactivate
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="" class="action-form">
                                        <input type="hidden" name="staff_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="activate-btn">
                                            <i class="fas fa-user-check"></i> Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="" class="action-form"
                                    onsubmit="return confirm('Are you sure you want to remove this staff member? This action cannot be undone.');">
                                    <input type="hidden" name="staff_id" value="<?php echo $member['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="remove-btn">
                                        <i class="fas fa-user-times"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>