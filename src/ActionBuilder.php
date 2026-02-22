<?php
 
namespace AmjadIqbal\NinjaKeys;

class ActionBuilder
{
    protected Manager $manager;
    protected string $id;

    public function __construct(Manager $manager, string $id)
    {
        $this->manager = $manager;
        $this->id = $id;
    }

    public function set(string $key, mixed $value): self
    {
        $this->manager->upsert($this->id, [$key => $value]);
        return $this;
    }

    public function title(string $title): self
    {
        return $this->set('title', $title);
    }

    public function description(string $description): self
    {
        return $this->set('description', $description);
    }

    public function icon(string $iconHtml): self
    {
        return $this->set('icon', $iconHtml);
    }

    public function mdIcon(string $name): self
    {
        return $this->set('mdIcon', $name);
    }

    public function href(string $href): self
    {
        return $this->set('href', $href);
    }

    public function target(string $target): self
    {
        return $this->set('target', $target);
    }

    public function parent(string $parentId): self
    {
        return $this->set('parent', $parentId);
    }

    public function children(array $ids): self
    {
        return $this->set('children', $ids);
    }

    public function shortcut(string $shortcut): self
    {
        return $this->set('shortcut', $shortcut);
    }

    public function hotkey(string $hotkey): self
    {
        return $this->set('hotkey', $hotkey);
    }

    public function section(string $section): self
    {
        return $this->set('section', $section);
    }

    public function handler(string $functionName): self
    {
        return $this->set('handler', $functionName);
    }

    public function can(string|array $ability): self
    {
        return $this->set('can', $ability);
    }

    public function keywords(string $keywords): self
    {
        return $this->set('keywords', $keywords);
    }
}
