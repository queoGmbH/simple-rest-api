# Configuration file for the Sphinx documentation builder.
# For TYPO3 Extension: Simple REST API

import sys
import os
from datetime import datetime

# -- Project information -----------------------------------------------------

project = 'Simple REST API'
copyright = f'{datetime.now().year}, Queo Group'
author = 'Sebastian Hofer'
version = '0.2.0'
release = '0.2.0-rc1'

# -- General configuration ---------------------------------------------------

extensions = [
    'sphinx.ext.intersphinx',
    'sphinx.ext.todo',
]

templates_path = ['_templates']
exclude_patterns = [
    '_build',
    'Thumbs.db',
    '.DS_Store',
    '**.sw*',
    'Includes.rst.txt',
]

# The suffix(es) of source filenames.
source_suffix = {
    '.rst': 'restructuredtext',
}

# The master toctree document.
master_doc = 'Index'

# -- Options for HTML output -------------------------------------------------

html_theme = 'sphinx_rtd_theme'
html_static_path = []
html_logo = None
html_favicon = None

html_theme_options = {
    'logo_only': False,
    'display_version': True,
    'prev_next_buttons_location': 'bottom',
    'style_external_links': True,
    'navigation_depth': 4,
    'includehidden': True,
    'titles_only': False,
}

html_context = {}

# -- Options for LaTeX output ------------------------------------------------

latex_elements = {}
latex_documents = [
    (master_doc, 'SimpleRestApi.tex', 'Simple REST API Documentation',
     'Sebastian Hofer \\& Queo Group', 'manual'),
]

# -- Options for manual page output ------------------------------------------

man_pages = [
    (master_doc, 'simplerestapi', 'Simple REST API Documentation',
     [author], 1)
]

# -- Options for Texinfo output ----------------------------------------------

texinfo_documents = [
    (master_doc, 'SimpleRestApi', 'Simple REST API Documentation',
     author, 'SimpleRestApi', 'REST API framework for TYPO3.',
     'Miscellaneous'),
]

# -- Options for intersphinx -------------------------------------------------

intersphinx_mapping = {
    't3coreapi': ('https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/', None),
    't3tca': ('https://docs.typo3.org/m/typo3/reference-tca/main/en-us/', None),
    't3tsref': ('https://docs.typo3.org/m/typo3/reference-typoscript/main/en-us/', None),
}

# -- Options for todo extension ----------------------------------------------

todo_include_todos = True

# -- Syntax highlighting -----------------------------------------------------

pygments_style = 'sphinx'
