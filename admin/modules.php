<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Module.php';
require_once '../classes/User.php';

// Set page title
$pageTitle = 'Manage Modules';

// Include header
include_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../employee/index.php');
    exit;
}

// Initialize classes
$moduleObj = new Module();
$userObj = new User();

// Get all users for assignment
$users = $userObj->getAllUsers();

// Process form submissions
$message = '';
$error = '';

// Handle module actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update module
    if (isset($_POST['save_module'])) {
        $moduleData = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'created_by' => $_SESSION['user_id'],
            'deadline' => !empty($_POST['deadline']) ? $_POST['deadline'] : null,
            'status' => $_POST['status']
        ];
        
        // Update existing module
        if (!empty($_POST['module_id'])) {
            $moduleData['module_id'] = $_POST['module_id'];
            if ($moduleObj->updateModule($moduleData)) {
                $message = 'Module updated successfully.';
            } else {
                $error = 'Failed to update module.';
            }
        } 
        // Create new module
        else {
            $moduleId = $moduleObj->createModule($moduleData);
            if ($moduleId) {
                $message = 'Module created successfully.';
                // Redirect to edit page to add lessons and quiz
                header("Location: module_detail.php?id=$moduleId");
                exit;
            } else {
                $error = 'Failed to create module.';
            }
        }
    }
    
    // Assign module to users
    if (isset($_POST['assign_module'])) {
        $moduleId = $_POST['module_id'];
        $selectedUsers = $_POST['selected_users'] ?? [];
        
        $successCount = 0;
        foreach ($selectedUsers as $userId) {
            if ($moduleObj->assignModuleToUser($moduleId, $userId)) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $message = "Module assigned to $successCount user(s) successfully.";
        } else {
            $error = 'Failed to assign module to users.';
        }
    }
    
    // Delete module
    if (isset($_POST['delete_module'])) {
        $moduleId = $_POST['module_id'];
        if ($moduleObj->deleteModule($moduleId)) {
            $message = 'Module deleted successfully.';
        } else {
            $error = 'Failed to delete module.';
        }
    }
}

// Get module for editing
$editModule = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editModule = $moduleObj->getModuleById($_GET['id']);
}

// Get all modules
$modules = $moduleObj->getAllModules();
?>

<!-- Navigation -->
<?php include '../includes/admin_nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                        <i class="bi bi-plus-circle"></i> Add New Module
                    </button>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Modules List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Modules</h6>
                </div>
                <div class="card-body">
                    <?php if (count($modules) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Created By</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($module['title']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($module['description'], 0, 100)) . (strlen($module['description']) > 100 ? '...' : ''); ?></td>
                                            <td><?php echo htmlspecialchars($module['creator_name']); ?></td>
                                            <td><?php echo $module['deadline'] ? date('M d, Y', strtotime($module['deadline'])) : 'No deadline'; ?></td>
                                            <td>
                                                <?php if ($module['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="module_detail.php?id=<?php echo $module['module_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModuleModal<?php echo $module['module_id']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#assignModuleModal<?php echo $module['module_id']; ?>">
                                                    <i class="bi bi-person-plus"></i> Assign
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModuleModal<?php echo $module['module_id']; ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                                
                                                <!-- Edit Module Modal -->
                                                <div class="modal fade" id="editModuleModal<?php echo $module['module_id']; ?>" tabindex="-1" aria-labelledby="editModuleModalLabel<?php echo $module['module_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editModuleModalLabel<?php echo $module['module_id']; ?>">Edit Module</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="title<?php echo $module['module_id']; ?>" class="form-label">Title</label>
                                                                        <input type="text" class="form-control" id="title<?php echo $module['module_id']; ?>" name="title" value="<?php echo htmlspecialchars($module['title']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="description<?php echo $module['module_id']; ?>" class="form-label">Description</label>
                                                                        <textarea class="form-control" id="description<?php echo $module['module_id']; ?>" name="description" rows="3"><?php echo htmlspecialchars($module['description']); ?></textarea>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="deadline<?php echo $module['module_id']; ?>" class="form-label">Deadline (optional)</label>
                                                                        <input type="date" class="form-control" id="deadline<?php echo $module['module_id']; ?>" name="deadline" value="<?php echo $module['deadline'] ? date('Y-m-d', strtotime($module['deadline'])) : ''; ?>">
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="status<?php echo $module['module_id']; ?>" class="form-label">Status</label>
                                                                        <select class="form-select" id="status<?php echo $module['module_id']; ?>" name="status" required>
                                                                            <option value="active" <?php echo $module['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                            <option value="inactive" <?php echo $module['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="save_module" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Assign Module Modal -->
                                                <div class="modal fade" id="assignModuleModal<?php echo $module['module_id']; ?>" tabindex="-1" aria-labelledby="assignModuleModalLabel<?php echo $module['module_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="assignModuleModalLabel<?php echo $module['module_id']; ?>">Assign Module to Users</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Select Users</label>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input select-all" type="checkbox" id="selectAll<?php echo $module['module_id']; ?>">
                                                                            <label class="form-check-label" for="selectAll<?php echo $module['module_id']; ?>">
                                                                                Select All
                                                                            </label>
                                                                        </div>
                                                                        <hr>
                                                                        <?php foreach ($users as $user): ?>
                                                                            <?php if ($user['role'] === 'employee'): ?>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input user-checkbox" type="checkbox" name="selected_users[]" value="<?php echo $user['user_id']; ?>" id="user<?php echo $module['module_id']; ?>_<?php echo $user['user_id']; ?>">
                                                                                    <label class="form-check-label" for="user<?php echo $module['module_id']; ?>_<?php echo $user['user_id']; ?>">
                                                                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                                                                    </label>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="assign_module" class="btn btn-success">Assign Module</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Module Modal -->
                                                <div class="modal fade" id="deleteModuleModal<?php echo $module['module_id']; ?>" tabindex="-1" aria-labelledby="deleteModuleModalLabel<?php echo $module['module_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModuleModalLabel<?php echo $module['module_id']; ?>">Delete Module</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the module "<?php echo htmlspecialchars($module['title']); ?>"?</p>
                                                                <p class="text-danger">This action cannot be undone. All associated lessons, quizzes, and user progress will be deleted.</p>
                                                                
                                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_module" class="btn btn-danger">Delete Module</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No modules found. Create one using the "Add New Module" button.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1" aria-labelledby="addModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModuleModalLabel">Add New Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline (optional)</label>
                        <input type="date" class="form-control" id="deadline" name="deadline">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_module" class="btn btn-primary">Create Module</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script>
    // Select all checkboxes functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckboxes = document.querySelectorAll('.select-all');
        
        selectAllCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const modal = this.closest('.modal');
                const userCheckboxes = modal.querySelectorAll('.user-checkbox');
                
                userCheckboxes.forEach(function(userCheckbox) {
                    userCheckbox.checked = checkbox.checked;
                });
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>

