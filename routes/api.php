<?php

// All Discord-facing routes live in web.php because Vercel strips /api from
// REQUEST_URI before PHP receives it, making routes defined here unreachable
// from external /api/* URLs.
