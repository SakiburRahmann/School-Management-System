<?php
/**
 * Admin - Event Management
 */

// 1. Core Includes & Auth
require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$eventModel = new Event();

// 2. Handle Action (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description']),
            'event_date' => $_POST['event_date'],
            'location' => sanitize($_POST['location']),
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        ];
        
        if (!empty($data['title']) && !empty($data['event_date'])) {
            $eventId = $eventModel->create($data);
            if ($eventId) {
                setFlash('success', 'Event added successfully!');
            } else {
                setFlash('danger', 'Failed to add event.');
            }
        }
    }
    redirect(BASE_URL . '/admin/events/');
}

// 3. Handle Action (Delete)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $eventId = $_GET['id'];
    if ($eventModel->delete($eventId)) {
        setFlash('success', 'Event deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete event.');
    }
    redirect(BASE_URL . '/admin/events/');
}

// 4. Data Fetching
$upcomingEvents = $eventModel->getUpcoming();
$pastEvents = $eventModel->getPast(10); // Limit past events to 10

// 5. Output View
$pageTitle = 'Manage Events';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="row">
    <!-- Add Event Form -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h3 class="card-title text-primary"><i class="fas fa-calendar-plus mr-2"></i> Add New Event</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_event">
                    
                    <div class="form-group">
                        <label for="title" class="font-weight-bold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Annual Sports Day" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date" class="font-weight-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" id="event_date" name="event_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="location" class="font-weight-bold">Location</label>
                        <input type="text" id="location" name="location" class="form-control" placeholder="e.g. School Auditorium">
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="font-weight-bold">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Event details..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1" checked>
                            <label class="custom-control-label font-weight-bold" for="is_public">Show on Website</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">
                        <i class="fas fa-save mr-2"></i> Save Event
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="col-lg-8">
        <!-- Upcoming Events -->
        <h4 class="mb-3 text-dark font-weight-bold">Upcoming Events</h4>
        <?php if (!empty($upcomingEvents)): ?>
            <div class="row">
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm event-card hover-elevate">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <!-- Decorative Badge -->
                                <div class="position-absolute" style="top: 0; right: 0; width: 80px; height: 80px; background: linear-gradient(135deg, transparent 50%, rgba(66, 153, 225, 0.1) 50%);"></div>
                                
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="date-badge text-center rounded p-2 bg-light border border-primary text-primary">
                                        <div class="small text-uppercase font-weight-bold"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                        <div class="h4 mb-0 font-weight-bold"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/admin/events/?delete=1&id=<?php echo $event['event_id']; ?>" onclick="return confirmDelete('Delete this event?');">
                                                <i class="fas fa-trash mr-2"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="card-title font-weight-bold mb-2">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </h5>
                                
                                <?php if (!empty($event['location'])): ?>
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt mr-1 text-danger"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="card-text text-secondary line-clamp-2 small">
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </p>
                                
                                <?php if ($event['is_public']): ?>
                                    <span class="badge badge-success px-2 py-1 footer-badge">
                                        <i class="fas fa-globe mr-1"></i> Public
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center py-4 border-0 shadow-sm mb-5">
                <i class="far fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="mb-0 text-muted">No upcoming events scheduled.</p>
            </div>
        <?php endif; ?>

        <!-- Past Events -->
        <?php if (!empty($pastEvents)): ?>
            <h4 class="mb-3 text-dark font-weight-bold border-top pt-4">Recent Past Events</h4>
            <div class="list-group shadow-sm rounded-lg overflow-hidden">
                <?php foreach ($pastEvents as $event): ?>
                    <div class="list-group-item list-group-item-action p-3">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="text-muted mr-3 font-weight-bold" style="width: 50px; text-align: center; line-height: 1.2;">
                                    <?php echo date('M', strtotime($event['event_date'])); ?><br>
                                    <span class="h5"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                </span>
                                <div>
                                    <h6 class="mb-1 font-weight-bold text-secondary"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($event['location'] ?? 'No Location'); ?></small>
                                </div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/admin/events/?delete=1&id=<?php echo $event['event_id']; ?>" 
                               class="btn btn-outline-danger btn-sm rounded-circle shadow-none"
                               onclick="return confirmDelete('Delete this event?');"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-elevate:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .date-badge { min-width: 60px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .footer-badge { position: absolute; bottom: 1.5rem; right: 1.5rem; font-size: 0.75rem; }
    .event-card .card-body { padding-bottom: 3.5rem !important; } /* Space for badge */
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
