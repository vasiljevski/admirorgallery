load("@rules_pkg//pkg:mappings.bzl", "pkg_attributes", "pkg_filegroup", "pkg_files", "pkg_mkdirs", "strip_prefix")
load("@rules_pkg//pkg:zip.bzl", "pkg_zip")

pkg_zip(
    name = "com_admirorgallery",
    strip_prefix = ".",
    srcs = [
        ":files",
    ],
)

filegroup(
    name = "files",
    srcs = glob([
        "com_admirorgallery/**",
    ]),

)