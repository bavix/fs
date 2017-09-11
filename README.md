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
