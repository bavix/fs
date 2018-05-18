# fs

nginx config

```nginx
server {

    # ...

	location /md0 {
		alias /mnt/md0;
		internal;
	}

    location /tmp {
        alias /tmp;
        internal;
    }
    
    # ...

}
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)
