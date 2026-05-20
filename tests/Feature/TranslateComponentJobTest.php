<?php

use App\Jobs\TranslateComponent;
use Illuminate\Support\Facades\Queue;

it('is dispatchable and queueable', function () {
    Queue::fake();

    TranslateComponent::dispatch(1, 'home', 'hero');

    Queue::assertPushed(TranslateComponent::class, function (TranslateComponent $job): bool {
        return $job->vendorId === 1
            && $job->pageSlug === 'home'
            && $job->componentType === 'hero';
    });
});

it('produces a unique identifier per vendor+page+type', function () {
    $job = new TranslateComponent(7, 'home', 'hero');

    expect($job->uniqueId())->toBe('translate:7:home:hero')
        ->and($job->uniqueFor())->toBe(60);
});
