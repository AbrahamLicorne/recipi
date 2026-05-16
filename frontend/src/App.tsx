import { useEffect, useState } from 'react'
import { AppBar, Box, Button, Container, CssBaseline, Toolbar, Typography } from '@mui/material'
import { ThemeProvider } from '@mui/material/styles'
import { theme } from './theme'
import { api } from './api/client'

function App() {
  const [ping, setPing] = useState<string>('—')

  useEffect(() => {
    api.get('/api/ping')
      .then((res) => setPing(JSON.stringify(res.data)))
      .catch((err) => setPing(`error: ${err.message}`))
  }, [])

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <AppBar position="static">
        <Toolbar>
          <Typography variant="h6" sx={{ flexGrow: 1 }}>
            Recipi
          </Typography>
          <Button color="inherit">Login</Button>
        </Toolbar>
      </AppBar>
      <Container sx={{ mt: 4 }}>
        <Typography variant="h4" gutterBottom>
          Welcome
        </Typography>
        <Typography variant="body1">API says: {ping}</Typography>
      </Container>
    </ThemeProvider>
  )
}

export default App
