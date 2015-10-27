/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.Estado;
import com.compreingressos.knowledge.model.Evento;
import com.compreingressos.knowledge.model.Genero;
import com.compreingressos.knowledge.model.Local;
import com.compreingressos.knowledge.model.Municipio;
import java.util.ArrayList;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;
import javax.persistence.Query;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class EventoFacade extends AbstractFacade<Evento> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public EventoFacade() {
        super(Evento.class);
    }

    @Override
    public List<Evento> findAll() {
        return getEntityManager().createNamedQuery("Evento.findAll").getResultList();
    }

    public List<Evento> findAllByLocal(Local local) {
        return getEntityManager().createNamedQuery("Evento.findAllByLocal").setParameter("local", local).getResultList();
    }
    
    public List<Evento> findAllByApresentacao(){                        
        Query query = getEntityManager().createNativeQuery("SELECT DISTINCT E.ID_KBEV, E.DS_KBEV_COMPLETO, "
                + "L.DS_KBLO, ES.DS_ESTADO, M.DS_MUNICIPIO, G.DS_KBGE "
                + "FROM DIM_KBASE_EVENTO E "
                + "INNER JOIN FATO_KBASE_APRESENTACAO A ON A.ID_KBEV = E.ID_KBEV "
                + "INNER JOIN DIM_KBASE_LOCAL L ON L.ID_KBLO = E.ID_KBLO "
                + "INNER JOIN DIM_MUNICIPIO M ON M.ID_MUNICIPIO = L.ID_MUNICIPIO "
                + "INNER JOIN DIM_ESTADO ES ON ES.ID_ESTADO = M.ID_ESTADO "
                + "INNER JOIN DIM_KBASE_GENERO G ON G.ID_KBGE = E.ID_KBGE "
                + "WHERE A.DT_KBAP_APRESENTACAO > GETDATE() "
                + "ORDER BY E.DS_KBEV_COMPLETO");
        List<Object[]> result = query.getResultList();
        List<Evento> eventos = new ArrayList<>();
        for(Object[] obj : result){
            Evento evento = new Evento();
            evento.setId(Integer.parseInt(obj[0].toString()));
            evento.setDescricaoCompleta(obj[1].toString());
            Local local = new Local();
            local.setDescricao(obj[2].toString());            
            Estado estado = new Estado();
            estado.setDescricao(obj[3].toString());
            Municipio municipio = new Municipio();
            municipio.setDescricao(obj[4].toString());
            municipio.setEstado(estado);
            local.setMunicipio(municipio);
            evento.setLocal(local);
            Genero genero = new Genero();
            genero.setDescricao(obj[5].toString());
            evento.setGenero(genero);
            eventos.add(evento);
        }
        return eventos;
    }
    
}
