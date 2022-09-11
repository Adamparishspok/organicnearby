# remove

```php
remove ( )
```

Removes the current node recursively from the DOM.
Does nothing if the node has no parent (root node);

**Example**

```php
$html = str_get_html(<<<EOD
<html>
<body>

	  Title
	  Row 1

</body>
</html>
EOD
);

$table = $html->find('table', 0);
$table->remove();

echo $html;

/**
 * Returns
 *
 * <html> <body>  </body> </html>
 */
```

**Remarks**

- Whitespace immediately **before** the removed node will remain in the DOM.
