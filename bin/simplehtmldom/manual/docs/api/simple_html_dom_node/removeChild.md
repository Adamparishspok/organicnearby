# removeChild

```php
removeChild ( object $node )
```

| Parameter | Description                                                                  |
| --------- | ---------------------------------------------------------------------------- |
| `node`    | Node to remove from current element, must be a child of the current element. |

Removes the node recursively from the DOM.
Does nothing if the provided node is not a child of the current node.

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

$body = $html->find('body', 0);
$body->removeChild($body->find('table', 0));

echo $html;

/**
 * Returns
 *
 * <html> <body>  </body> </html>
 */
```

**Remarks**

- Whitespace immediately **before** the removed node will remain in the DOM.
