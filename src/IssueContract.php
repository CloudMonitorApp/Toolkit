<?php

namespace CloudMonitor\Toolkit;

interface IssueContract
{
    public function getMessage(): string;
    public function getLine(): int;
    public function getFile(): string;
    public function getSeverity(): int;
    public function getCode(): int;
    public function getClass(): string;
    public function getMethod(): string;
    public function getPrevious(): string;
    public function getPreview(): array;
    public function getUrl(): string;
    public function getTrace(): array;
}