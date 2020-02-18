<?php

namespace Core\View\Exceptions;

class View
{
    /** @var bool $disableCache */
    protected $disableCache = true;
    /** @var string $view */
    protected $view;
    /** @var array $params */
    protected $params;

    public function __construct(string $view, array $params = [])
    {
        $this->view = $view;
        $this->params = $params;

        if (false === $this->hasViewCachePath() || $this->disableCache) {
            $this->setViewCache($this->compile($this->view));
        }
    }

    /**
     * Crée un rendu de la vue depuis sa version compilée
     * en y incluant les variables requises
     *
     * @return string
     */
    protected function render(): string
    {
        ob_start();

        extract($this->params);
        require_once $this->getViewCachePath();
        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

    /**
     * Compile la vue en PHP
     *
     * @param string|null $view
     * @return string
     * @throws ViewNotFoundException
     */
    protected function compile(?string $view = null): string
    {
        ob_start();

        require_once $this->getViewPath($view);;
        $content = $this->parse(ob_get_contents());

        ob_end_clean();

        return $content;
    }

    /**
     * Retourne le chemin réel vers la vue
     *
     * @param string $name
     * @return string
     * @throws ViewNotFoundException
     */
    protected function getViewPath(string $name): string
    {
        $view = str_replace('.', '/', $name);
        $view = base_path('views/' . $view . '.html');

        if (file_exists($view)) {
            return $view;
        }

        throw new ViewNotFoundException(sprintf("La vue %s n'existe pas", $view));
    }

    /**
     * Retourne le chemin réel vers la version compilée de la vue
     *
     * @return string
     */
    protected function getViewCachePath(): string
    {
        $name = md5($this->view);
        return base_path('storage/cache/views/' . $name . '.php');
    }

    /**
     * Vérifie que la vue existe
     *
     * @return bool
     */
    protected function hasViewCachePath(): bool
    {
        return file_exists($this->getViewCachePath());
    }

    /**
     * Enregistre la vue compilée
     *
     * @param string $content
     * @return bool
     */
    protected function setViewCache(string $content): bool
    {
        return file_put_contents($this->getViewCachePath(), $content) !== false;
    }

    /**
     * Transforme le contenu de la vue en PHP
     *
     * @param string $content
     * @return string
     */
    protected function parse(string $content): string
    {
        // Parse les conditions
        $content = preg_replace('/@if\s?\((.*)\)/iu', '<?php if($1): ?>', $content);
        $content = preg_replace('/@elseif\s?\((.*)\)/iu', '<?php elseif($1): ?>', $content);
        $content = preg_replace('/@else/iu', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/iu', '<?php endif; ?>', $content);

        // Parse les loops
        $content = preg_replace('/@foreach\s?\((.*)\)/iu', '<?php foreach($1): ?>', $content);
        $content = preg_replace('/@endforeach/iu', '<?php endforeach; ?>', $content);
        $content = preg_replace('/@for\s?\((.*)\)/iu', '<?php for($1): ?>', $content);
        $content = preg_replace('/@endfor\s?\((.*)\)/iu', '<?php endfor; ?>', $content);
        $content = preg_replace('/@while\s?\((.*)\)/iu', '<?php while($1): ?>', $content);
        $content = preg_replace('/@endwhile\s?\((.*)\)/iu', '<?php endwhile; ?>', $content);

        // Parse include
        $content = preg_replace_callback('/@include\s?\([\'|\"](.*)[\'|\"]\)/iu', function ($matches) {
            return $this->compile($matches[1]);
        }, $content);

        // Parse vars
        $content = preg_replace('/@{{\s?(.*)\s?}}/iu', '<?= $1; ?>', $content);
        $content = preg_replace('/{{\s?(.*)\s?}}/iu', '<?= escape($1); ?>', $content);

        return $content;
    }

    /**
     * Retourne le contenu de la vue
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
