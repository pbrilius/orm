<?php

declare(strict_types=1);

namespace App\View;

class ViewRenderer
{
    private string $templatePath;

    public function __construct(string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? dirname(__DIR__) . '/templates';
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->templatePath . '/' . $template . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        extract($data);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    public function renderWithLayout(string $template, array $data = [], string $layout = 'layout'): string
    {
        $content = $this->render($template, $data);
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }
}
