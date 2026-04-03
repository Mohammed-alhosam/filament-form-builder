<?php

namespace Alhosam\FilamentFormBuilder\Tests\Feature;

use Alhosam\FilamentFormBuilder\Models\Form;
use Alhosam\FilamentFormBuilder\Services\FormSubmissionService;
use Alhosam\FilamentFormBuilder\Tests\TestCase;

class FormSubmissionServiceTest extends TestCase
{
    public function test_submission_service_stores_submission_payload_and_context(): void
    {
        $form = Form::create([
            'name' => ['en' => 'Registration form'],
            'slug' => 'registration-form',
            'status' => 'published',
            'is_active' => true,
            'submit_label' => ['en' => 'Submit'],
        ]);

        $submission = app(FormSubmissionService::class)->submit($form, [
            'full_name' => 'Example User',
            'email' => 'example@example.com',
        ], [
            'status' => 'submitted',
            'locale' => 'en',
            'meta' => ['channel' => 'test'],
        ]);

        $this->assertDatabaseHas('form_builder_submissions', [
            'id' => $submission->getKey(),
            'form_id' => $form->getKey(),
            'status' => 'submitted',
            'locale' => 'en',
        ]);

        $this->assertSame('Example User', $submission->payload['full_name']);
        $this->assertSame('test', $submission->meta['channel']);
    }
}
