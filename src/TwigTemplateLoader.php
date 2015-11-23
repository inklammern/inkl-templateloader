<?php

namespace Inkl\TwigTemplateLoader;


use Aura\Router\Exception;

class TwigTemplateLoader implements \Twig_LoaderInterface
{
	/** @var array */
	private $paths = [];

	/** @var array */
	private $themes = [];

	/**
	 * TwigTemplateLoader constructor.
	 * @param array $themes
	 */
	public function __construct(array $themes)
	{
		$this->themes = $themes;
	}


	public function addPath($path, $namespace) {
		$this->paths[$namespace] = $path;
	}


	/**
	 * Gets the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to load
	 *
	 * @return string The template source code
	 *
	 * @throws \Twig_Error_Loader When $name is not found
	 */
	public function getSource($name)
	{
		return file_get_contents($this->findTemplate($name));
	}

	/**
	 * Gets the cache key to use for the cache for a given template name.
	 *
	 * @param string $name The name of the template to load
	 *
	 * @return string The cache key
	 *
	 * @throws \Twig_Error_Loader When $name is not found
	 */
	public function getCacheKey($name)
	{
		return $this->findTemplate($name);
	}

	/**
	 * Returns true if the template is still fresh.
	 *
	 * @param string $name The template name
	 * @param int $time Timestamp of the last modification time of the
	 *                     cached template
	 *
	 * @return bool true if the template is fresh, false otherwise
	 *
	 * @throws \Twig_Error_Loader When $name is not found
	 */
	public function isFresh($name, $time)
	{
		return filemtime($this->findTemplate($name)) <= $time;
	}


	/**
	 * Find template
	 *
	 * @param $name
	 * @return string
	 * @throws Exception
	 * @throws \Twig_Error_Loader
	 */
	protected function findTemplate($name) {

		$nameParts = explode('/', $name);
		if (count($nameParts) < 2)
		{
			return '';
		}

		$namespace = str_replace('@', '', array_shift($nameParts));
		$filename = implode('/', $nameParts);

		if (!isset($this->paths[$namespace]))
		{
			throw new \Twig_Error_Loader(sprintf('namespace "%s" not found', $namespace));
		}

		$path = $this->paths[$namespace];
		foreach ($this->themes as $theme) {
			if ($file = realpath($path . '/' . $theme . '/views/' . $filename)) {
				return $file;
			}
		}

		throw new \Twig_Error_Loader(sprintf('"%s" not found', $name));
	}

}
