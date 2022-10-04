# php_relay_control
### Web interface for [USR-IOT-R16 16-channel electric relay board](https://www.amazon.in/USR-IOT-USR-R16-T-Industrial-Interface/dp/B01DEVRUNG)
Max.Fischer dev@monologic.ru


![Web Interface for IO 16-channel relay board](/io-web-board.png?raw=true "Web interface - IO board")

For details refer to ![GPIO Controller command protocol](/PROTO.md "GPIO Protocol description")


### Channel control block
```html

```

### Custom action button block
```html
<table class="io-control-container">
  <tr>
    <td>
      <div class="io-control-icon">
        <i class="fa fa-lock" aria-hidden="true"></i>
      </div>
      <div class="io-control-label"> Custom Action</div>
    </td><td align="right">
      <div class="io-custom-action" io-custom-script='http://10.0.10.29/scripts/action.php?action_id=6'>
        <div class="custom-action-label">activate</div>
      </div>
    </td>
  </tr>
</table>
```
