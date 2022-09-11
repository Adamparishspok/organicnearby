function fileValidation(fi, maxSizeMB=4)
	{
	// Check if any file is selected.
	if (fi.files.length > 0)
		{
		for (var i = 0; i <= fi.files.length - 1; i++)
			{
			const fsize = fi.files.item(i).size;
			const file = Math.round((fsize / 1024));
			// The size of the file.
			if (file >= (maxSizeMB*1024))
				{
				saySize = Math.round(file / 1024);
				alert("File is " + saySize + "MB in size, please select a file less than " + maxSizeMB + ' MB');
				return false;
				}
			}
		}
	return true;
	}
