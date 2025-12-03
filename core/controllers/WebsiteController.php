<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/models/Notice.php';
require_once __DIR__ . '/../../core/models/Event.php';
require_once __DIR__ . '/../../core/models/AdmissionRequest.php';
require_once __DIR__ . '/../../core/models/ContactMessage.php';
require_once __DIR__ . '/../../core/models/GalleryImage.php';

class WebsiteController extends Controller
{
    public function home(): void
    {
        $noticeModel = new Notice();
        $eventModel = new Event();

        $notices = [];
        $events = [];

        try {
            $notices = $noticeModel->latest(5);
        } catch (Throwable $e) {
            $notices = [];
        }

        try {
            $events = $eventModel->upcoming(5);
        } catch (Throwable $e) {
            $events = [];
        }

        $this->render('public/home', [
            'notices' => $notices,
            'events' => $events,
        ]);
    }

    public function about(): void
    {
        $this->render('public/about');
    }

    public function academics(): void
    {
        $this->render('public/academics');
    }

    public function admissions(): void
    {
        $this->render('public/admissions', ['success' => null, 'error' => null]);
    }

    public function submitAdmission(): void
    {
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'student_name' => trim($_POST['student_name'] ?? ''),
                'class_applied' => trim($_POST['class_applied'] ?? ''),
                'guardian_name' => trim($_POST['guardian_name'] ?? ''),
                'guardian_phone' => trim($_POST['guardian_phone'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
            ];

            if ($data['student_name'] === '' || $data['class_applied'] === '') {
                $error = 'Please fill in all required fields.';
            } else {
                try {
                    $model = new AdmissionRequest();
                    if ($model->create($data)) {
                        $success = 'Your admission request has been submitted successfully.';
                    } else {
                        $error = 'Failed to submit your request. Please try again later.';
                    }
                } catch (Throwable $e) {
                    $error = 'An error occurred while saving your request.';
                }
            }
        }

        $this->render('public/admissions', compact('success', 'error'));
    }

    public function news(): void
    {
        $eventModel = new Event();
        $events = [];
        try {
            $events = $eventModel->upcoming(50);
        } catch (Throwable $e) {
            $events = [];
        }
        $this->render('public/news', compact('events'));
    }

    public function notices(): void
    {
        $noticeModel = new Notice();
        $notices = [];
        try {
            $notices = $noticeModel->latest(50);
        } catch (Throwable $e) {
            $notices = [];
        }
        $this->render('public/notices', compact('notices'));
    }

    public function gallery(): void
    {
        $galleryModel = new GalleryImage();
        $images = [];
        try {
            $images = $galleryModel->all();
        } catch (Throwable $e) {
            $images = [];
        }
        $this->render('public/gallery', compact('images'));
    }

    public function contact(): void
    {
        $this->render('public/contact', ['success' => null, 'error' => null]);
    }

    public function submitContact(): void
    {
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'subject' => trim($_POST['subject'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
            ];

            if ($data['name'] === '' || $data['email'] === '' || $data['message'] === '') {
                $error = 'Please fill in all required fields.';
            } else {
                try {
                    $model = new ContactMessage();
                    if ($model->create($data)) {
                        $success = 'Your message has been sent. We will get back to you soon.';
                    } else {
                        $error = 'Failed to send your message. Please try again later.';
                    }
                } catch (Throwable $e) {
                    $error = 'An error occurred while sending your message.';
                }
            }
        }

        $this->render('public/contact', compact('success', 'error'));
    }
}


