<?php

class Search {

	private $limit = 3000;

	public function __construct() {
	}

	public function queryFile($file, $unique) {
		if ($file === null) {
			throw new Exception('File is NULL');
		}

		$sqlNonUnique = <<<EOF
SELECT
	dir.name AS category,
	pkg.name AS package,
	file.path AS path,
	file.name AS file,
	misc AS type,
	STRING_AGG(DISTINCT arch.name, ', ' ORDER BY arch.name) AS archs,
	pkg.version AS version,
	STRING_AGG(DISTINCT useflag.name, ', ' ORDER BY useflag.name) AS useflags
FROM
	public.file
LEFT JOIN public.pkg ON file.fk_pkgid = pkg.pkgid
LEFT JOIN public.dir ON pkg.fk_dirid = dir.dirid
LEFT JOIN public.file2arch ON file.fileid = file2arch.fk_fileid
LEFT JOIN public.arch ON arch.archid = file2arch.fk_archid
LEFT JOIN public.file2useflag ON file.fileid = file2useflag.fk_fileid
LEFT JOIN public.useflag ON useflag.useflagid = file2useflag.fk_useflagid
WHERE
	<file> <path>
GROUP BY
	category,
	package,
	path,
	file.name,
	misc,
	version
ORDER BY
	category,
	package,
	path,
	file.name
LIMIT
	%i2
EOF;

		$sqlUnique = <<<EOF
SELECT
	dir.name AS category,
	pkg.name AS package,
	file.path AS path,
	file.name AS file,
	STRING_AGG(DISTINCT misc, ', ' ORDER BY misc) AS type,
	STRING_AGG(DISTINCT arch.name, ', ' ORDER BY arch.name) AS archs,
	STRING_AGG(DISTINCT useflag.name, ', ' ORDER BY useflag.name) AS useflags
FROM
	public.file
LEFT JOIN public.pkg ON file.fk_pkgid = pkg.pkgid
LEFT JOIN public.dir ON pkg.fk_dirid = dir.dirid
LEFT JOIN public.file2arch ON file.fileid = file2arch.fk_fileid
LEFT JOIN public.arch ON arch.archid = file2arch.fk_archid
LEFT JOIN public.file2useflag ON file.fileid = file2useflag.fk_fileid
LEFT JOIN public.useflag ON useflag.useflagid = file2useflag.fk_useflagid
WHERE
	<file> <path>
GROUP BY
	category,
	package,
	path,
	file.name
ORDER BY
	category,
	package,
	path,
	file.name
LIMIT
	%i2
EOF;

		if ($unique) {
			$sql = $sqlUnique;
		} else {
			$sql = $sqlNonUnique;
		}

		if (strpos($file, '%') != false) {
			$sql = str_replace('<file>', 'file.name LIKE %s1', $sql);
		} else {
			$sql = str_replace('<file>', 'file.name = %s1', $sql);
		}
		
#		file_put_contents('/mnt/space/pfl/query.log', date('r') . ' ' . $file . PHP_EOL, FILE_APPEND);

		if (strpos($file, '/') !== false) {
			$where = 'AND file.path = %s3';
			/*if (strpos(dirname($file), '%') != false) {
				$where = 'AND file.path LIKE %s3';
			} else {
				$where = 'AND file.path = %s3';
			}*/
			
			$sql = str_replace('<path>', $where, $sql);
			return db::get('psql')->queryAndFetch($sql, basename($file), $this->limit, dirname($file));
		} else {
			$sql = str_replace('<path>', '', $sql);
			return db::get('psql')->queryAndFetch($sql, $file, $this->limit);
		}
	}

	public function queryPackageFiles($category, $package, $version) {
		if ($category === null) {
			throw new Exception('Category is NULL');
		}

		if ($package === null) {
			throw new Exception('Package is NULL');
		}

		if ($version === null) {
			throw new Exception('Version is NULL');
		}

$sql = <<<EOF
SELECT
	MAX(file.name) AS file,
	MAX(file.path) AS path,
	MAX(file.misc) AS type,
	STRING_AGG(DISTINCT arch.name, ', ' ORDER BY arch.name) AS archs,
	STRING_AGG(DISTINCT useflag.name, ', ' ORDER BY useflag.name) AS useflags
FROM
	public.pkg
LEFT JOIN public.dir ON dir.dirid = pkg.fk_dirid
LEFT JOIN public.file ON file.fk_pkgid = pkg.pkgid
LEFT JOIN public.file2arch ON file.fileid = file2arch.fk_fileid
LEFT JOIN public.arch ON arch.archid = file2arch.fk_archid
LEFT JOIN public.file2useflag ON file.fileid = file2useflag.fk_fileid
LEFT JOIN public.useflag ON useflag.useflagid = file2useflag.fk_useflagid
WHERE
	dir.name = %s1 AND
	pkg.name = %s2 AND
	pkg.version = %s3
GROUP BY
	file.fileid
EOF;

		return db::get('psql')->queryAndFetch($sql, $category, $package, $version);
	}

	public function queryPackageVersions($category, $package) {
		if ($category === null) {
			throw new Exception('Category is NULL');
		}

		if ($package === null) {
			throw new Exception('Package is NULL');
		}

$sql = <<<EOF
SELECT
	pkg.version AS version
FROM
	pkg
LEFT JOIN dir ON dir.dirid = pkg.fk_dirid
WHERE
	dir.name = %s1 AND
	pkg.name = %s2
ORDER BY
	pkg.version DESC
EOF;

		return db::get('psql')->queryAndFetch($sql, $category, $package);
	}
}

?>
